<?php

namespace Vbot\Msxiaoice;

use Hanson\Vbot\Console\Console;
use Hanson\Vbot\Extension\AbstractMessageHandler;
use Hanson\Vbot\Message\Text;
use Illuminate\Support\Collection;

class Msxiaoice extends AbstractMessageHandler
{

    public $name = 'msxiaoice';
    public $zhName = 'MS小冰';
    public $author = 'Y';
    public $version = '1.0.0';
    public $baseExtensions = [
        Http::class,
    ];

    public function register()
    {
        $default_config = [
            'status'        => true,
            'readurl'       => 'http://m.weibo.cn/msg/messages?uid=5175429989&page=1',
            'SUB'           => 'SUB=************',
            'sendurl'       => 'https://weibo.cn/msg/do/post?st=ca7727',
            'error_message' => '机器人失灵了，暂时没法陪聊了，T_T！',
        ];
        $this->config = array_merge($default_config, $this->config ?? []);
        $this->status = $this->config['status'];
    }

    public function handler(Collection $message)
    {
        if ($this->config['status'] && $message['type'] === 'text' && ($message['fromType'] === 'Friend' || $message['isAt'])) {
            $username = $message['from']['UserName'];

            //vbot('console')->log(json_encode($message), '微信消息');
            //临时cookies
            $cookie = dirname(__FILE__) . '/weibo.tmp';
            //post数据
            $options = array (
                'content'=>$message['pure'],
                'rl'=>'2',
                'uid'=>'5175429989',
                'send'=>'发送'
            );

                $curl = curl_init();//初始化curl模块
                curl_setopt($curl, CURLOPT_URL, $this->config['sendurl']);//登录提交的地址
                curl_setopt($curl, CURLOPT_HEADER, 0);//是否显示头信息
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);//是否自动显示返回的信息
                curl_setopt($curl, CURLOPT_COOKIE, $this->config['SUB']);
                curl_setopt($curl, CURLOPT_COOKIEJAR, $cookie); //设置Cookie信息保存在指定的文件中
                curl_setopt($curl, CURLOPT_POST, 1);//post方式提交
                curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($options));//要提交的信息
                curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
                curl_exec($curl);//执行cURL
                curl_close($curl);//关闭cURL资源，并且释放系统资源

                ////////////////////////////////////////////////////
                
                sleep(1);
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $this->config['readurl']);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_COOKIE, $this->config['SUB']);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                $ct = curl_exec($ch);
                curl_close($ch);
                $data=json_decode($ct,1);
                $m=$data['data'][0]['text'];
                vbot('console')->log(json_encode($m), '小冰消息');
                //$data = json_decode($response);
                //替换
                $m=str_replace("分享语音","对方给你发送了一段语音。暂时无法显示",$m);
                $m=str_replace("分享图片","对方给你发送了图片音。暂时无法显示",$m);

                if($message['isAt'])
                    Text::send($username, "@".$message['sender']["NickName"]." ".$m);
                else
                    Text::send($username, $m);
        }
    }
}