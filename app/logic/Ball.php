<?php

namespace App\Logic;

use App\Service\Ball\Apeironnft;
use App\Lib\Tools;

class Ball
{
    public function handle($items)
    {
        $count = [];
        $balls = [];
        foreach ($items as $item) {
            $rows = $item["data"]["items"];
            $src = $item["data"]["src"];
            $classNmae = basename(str_replace("\\", "/", $item["process"]));
            $count[$classNmae] = count($rows);
            $txt = sprintf("%s\r\n%s\r\n\r\n",$classNmae,json_encode($rows,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
            file_put_contents(DIR."log.txt",$txt,FILE_APPEND);
            foreach ($rows as &$row) {
                $row["src"] = $src;
                $prifix = $row["prifix"];
                $id = $row["id"];
                $key = "{$prifix}_{$id}";
                if (isset($balls[$key])) {
                    $other = $balls[$key];
                    if ($row["eth"] < $other["eth"]) {
                        $balls[$key] = $row;
                    }
                } else {
                    $balls[$key] = $row;
                }
            }
        }

        $Apeironnft = Apeironnft::getInstance();
        foreach ($balls as $key => $ball) {
            $eth = $ball["eth"];
            $usdt = $ball["usdt"];
            $breedCount = $ball["breedCount"];
            $src = $ball["src"];
            $prifix = $ball["prifix"];
            $name = $ball["name"];
            $ronin = $ball["ronin"];
            $id = $ball["id"];
            if(!$Apeironnft->checkBall($id,$prifix)){
                continue;
            }
            $ball = $Apeironnft->getBall($id,$prifix)
            ->setNowPrice($eth, $usdt,$ronin)
            ->setReproducecCount($breedCount)
            ->setSrc($src)
            ->setName($name);
            $Apeironnft->setBall($ball);
        }
        $Apeironnft->checkPrice();
        $messages = implode(PHP_EOL, $Apeironnft->messages);
        if ($messages) {
            Tools::sendMessage($messages);
            $Apeironnft->messages = [];
        }
        echo sprintf("市场情况:%s,监控时间:%s".PHP_EOL, json_encode($count, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES), date("Y-m-d H:i:s"));
    }

}
