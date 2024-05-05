<?php
include "../autoload.php";

exec("ps axo command |grep '^php' |grep -v grep|grep -v check",$output);
$message= date("Y-m-d H:i:s").PHP_EOL;
if(!$output){
    $tgmsg = "检测服务进程已退出，尝试重新拉起";
    App\Lib\Tools::sendMessage($tgmsg);

    exec("php /home/monitor/public/start.php>/dev/null &", $output);
    $message.= $tgmsg.PHP_EOL;
}else{
    $message.= "当前服务正常".PHP_EOL;

}

$message .= "doned".PHP_EOL;

file_put_contents(DIR."/check.log",$message);