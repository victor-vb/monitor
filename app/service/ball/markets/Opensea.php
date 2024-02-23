<?php

namespace App\Service\Ball\Markets;

class Opensea extends AbstractBall
{
    public function marketNTF()
    {
        $originBalls = $this->getOriginBalls();
        $derivedBalls =$this->getDerivedBalls();
        $balls = array_merge($originBalls, $derivedBalls);
        $data = [
            "items"=>$balls,
            "src"=>$this->src
        ];
        return $data;
    }

    public function getOriginBalls()
    {
        $path = str_replace("\\", "/", dirname(__FILE__));
        exec("node {$path}/Opensea.js", $response);
        $responseArray = json_decode($response[0], true);
        $balls = isset($responseArray["data"]["collectionItems"]["edges"]) ? $responseArray["data"]["collectionItems"]["edges"] : [];
        $items = [];
        foreach ($balls as $ball) {
            $node = $ball["node"];
            $id = $node["tokenId"];
            $prices = $node["orderData"]["bestAskV2"]["priceType"];
            $eth = sprintf("%.3f", $prices["eth"]);
            $usdt = sprintf("%.3f", $prices["usd"]);
            $ronin = sprintf("%.3f", $usdt/$this->rate["ron"]["usd"]);
            array_push($items, $this->getItem($id, "origin", $eth, $usdt, $ronin, -1));
        }
        return $items;
    }

    # todo
    public function getDerivedBalls()
    {
        return [];
    }

    public function setSrc()
    {
        $this->src = "opensea.io";
    }
}
