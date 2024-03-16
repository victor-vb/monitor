<?php

namespace App\Service\Ball\BallInfo;

use App\Service\Ball\Markets\Office;
use App\Service\Ball\Markets\Opensea;
use App\Service\Ball\Markets\Skymavis;
use App\Task\AbstractProcess;
use App\Lib\Tools;
use App\Service\Ball\Apeironnft;

class CacheAttrs extends AbstractProcess
{
    public function after()
    {
        sleep(12*60*60);
    }
    
    public function main()
    {
        $lists = [];
        $makerts = [
            new Office(),
            new Skymavis(),
            // new Opensea()
        ];
        $office = $makerts[0];
        $attrs = $office->loadAttrs();
        $uncompeleted_ids = [];
        foreach (Apeironnft::getInstance()->balls as $ball) {
            if (strtolower($ball->prifix) == "origin") {
                $attr = $office->getAttr($attrs,$ball->id);
                if (!$attr) {
                    array_push($uncompeleted_ids, $ball->id);
                }
            }
        }

        foreach ($uncompeleted_ids as $id) {
            echo "正在更新id:{$id}相关属性信息".PHP_EOL;
            foreach ($makerts as $market) {
                $attr = $market->getOriginBallInfo($id);
                if ($attr instanceof Attributes) {
                    $lists["origin_{$ball->id}"] = $attr;
                    break;
                }
            }
        }
        $office->saveAttrs($lists);
        return $lists;
    }
}
