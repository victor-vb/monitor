<?php

namespace App\Logic;

use App\Service\Ball\Apeironnft;
use App\Lib\Tools;
use App\Service\Ball\BallInfo\Attributes;

class Ball
{
    public function handle($items)
    {
        $count = [];
        $balls = [];
        $messages = [sprintf("date:%s",date("Y-m-d H:i:s"))];
        foreach ($items as $item) {
            $rows = $item["data"]["items"];
            $src = $item["data"]["src"];
            $classNmae = basename(str_replace("\\", "/", $item["process"]));
            $count[$classNmae] = count($rows);
            
            array_push($messages,$classNmae);
            foreach ($rows as &$row) {
                $row["src"] = $src;
                $row["caller"] = $item["process"];
                $prifix = $row["prifix"];
                $id = $row["id"];
                $breedCount = $row["breedCount"];
                $key = "{$prifix}_{$id}";
                $eth = $row["eth"];
                $ronin = $row["ronin"];
                $usdt = $row["usdt"];

                if (isset($balls[$key])) {
                    $other = $balls[$key];
                    if ($row["eth"] < $other["eth"]) {
                        $balls[$key] = $row;
                    }
                } else {
                    $balls[$key] = $row;
                }
                $log = sprintf("id:%s,E:%s,R:%s,U:%s,B:%s",$id,$eth,$ronin,$usdt,$breedCount);
                array_push($messages, $log);
            }
        }
        array_push($messages, "\n\n");

        file_put_contents(DIR."log.txt", implode(PHP_EOL,$messages), FILE_APPEND);

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
            $caller = $ball["caller"];
            $attrs = $ball["attrs"];
            if (!$Apeironnft->checkBall($id, $prifix)) {
                continue;
            }

            $ball = $Apeironnft->getBall($id, $prifix)
            ->setNowPrice($eth, $usdt, $ronin)
            ->setReproducecCount($breedCount)
            ->setSrc($src)
            ->setName($name)
            ->setCaller($caller);

            $attr = Attributes::from($attrs);
            if ($attr instanceof Attributes) {
                $level = $attr->checkAttributes();
                switch ($level) {
                    case 2:
                        if (in_array($ball->enname, ["Arcane","Mythic"])) {
                            $ball->setMaxPriceEthfloatUp(0.3);
                        }
                        break;
                    case 1:
                        if (in_array($ball->enname, ["Arcane","Mythic"])) {
                            $ball->setMaxPriceEthfloatUp(2);
                        }
                        if(in_array($ball->enname, ["Divine"])){
                             $ball->setMaxPriceEthfloatUp(1);
                        }
                        break;
                    default:

                }
                $ball->setAttr($attr);
            }
            $Apeironnft->setBall($ball);
        }
        $Apeironnft->checkPrice();
        $messages = implode(PHP_EOL.PHP_EOL, $Apeironnft->messages);
        if ($messages) {
            echo $messages.PHP_EOL;
            Tools::sendMessage($messages);
            $Apeironnft->messages = [];
        }
        echo sprintf("市场情况:%s,监控时间:%s".PHP_EOL, json_encode($count, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES), date("Y-m-d H:i:s"));
    }
}
