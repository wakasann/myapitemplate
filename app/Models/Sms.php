<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/15
 * Time: 17:14
 */

namespace App\Models;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;

/**
 * 發送短信消息 (Send sms logic Class)
 * @version 1.0
 * @package App
 * @category Class
 * @author wakasann<xiaofosong@126.com>
 */
class Sms
{
    private $accessyou_account = '';
    private $accessyou_pwd     = '';

    /**
     * 發送sms驗證碼
     * @param  string $msg urlencode message
     * @param  string $phone phone number
     */
    public function sendSms($msg,$phone,$zone='852'){

        if(strpos($phone,"+".$zone) === 0){
            $phone = str_replace("+".$zone, $zone, $phone);
        }else if(strpos($phone,$zone) !== 0){
            $phone = $zone.$phone;
        }


        $url = 'http://api.accessyou.com/sms/sendsms-utf8.php?msg='.$msg.'&phone='.$phone.'&pwd='.$this->accessyou_pwd.'&accountno='.$this->accessyou_account.'&from=demo';
        \Log::debug("url".$url);
        try{
            $client = new Client();
            $response = $client->get($url);
            $body = $response->getBody();
            $stringBody = (string) $body;
            \Log::debug('sms response'.$body.'___'.$stringBody);

        }catch(RequestException $e){
            \Log::error("send sms".$e->getMessage());
        }

    }

    public function crateSmsCode(){
        // generate a pin based on 2 * 7 digits + a random character
        $pin = mt_rand(100, 999)
            . mt_rand(100, 999);
        // shuffle the result
        $string = str_shuffle($pin);
        return $string;
    }
}