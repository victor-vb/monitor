<?php
namespace App\Lib;
class Tools{

    public static function curl($url, $params = false, $ispost = 0, $headers = [],$debug=false, $timeout = 10)
    {
        $httpInfo = array();
        $ch = curl_init();
        if($debug){
            curl_setopt($ch, CURLOPT_VERBOSE, 1);
        }
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 对认证证书来源的检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // 从证书中检查SSL加密算法是否存在
        if ($headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        if ($ispost) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_URL, $url);
        } else {
            if ($params) {
                if (is_array($params)) {
                    $params = http_build_query($params);
                }
                curl_setopt($ch, CURLOPT_URL, $url . '?' . $params);
            } else {
                curl_setopt($ch, CURLOPT_URL, $url);
            }
        }

        $response = curl_exec($ch);
        if ($response === false) {
            return 'Curl error: ' . curl_error($ch);
            // return false;
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $httpInfo = array_merge($httpInfo, curl_getinfo($ch));
        curl_close($ch);

        if($debug){
            $header = "";
            foreach($headers as $h){
                $header .= "-H '{$h}' ";
            }
            if(is_array($params)){
                $params = http_build_query($params);
            }
            $curl = "curl -d '{$params}' {$header} -X ".($ispost ? "POST" :"GET")." '{$url}'";
            file_put_contents(DIR."curl.sh",$curl,FILE_APPEND);
        }
        return $response;
    }

    public static function sendMessage($text){
        // https://api.telegram.org/bot<token>/getUpdates   获取群组id
        // https://api.telegram.org/bot<token>/sendMessage  发送消息
        $token = "6115387654:AAHelaTW7a6Yn7_19XF-rU1s6eO-ozBoQLA";
        $chat_id = "-4162627115";
        $api = "https://api.telegram.org/bot{$token}/sendMessage";
        $params = [
            "chat_id"=>$chat_id,
            "text"=>$text
        ];
        self::curl($api,$params,1);
    }
}