<?php

namespace App\Service\Ball\Markets;

use App\Service\Ball\BallInfo\Attributes;

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
        exec("node {$path}/Opensea.js getOriginBalls", $response);
        $responseArray = json_decode($response[0], true);
        $balls = isset($responseArray["data"]["collectionItems"]["edges"]) ? $responseArray["data"]["collectionItems"]["edges"] : [];
        $items = [];
        foreach ($balls as $ball) {
            $prifix = "origin";
            $node = $ball["node"];
            $id = $node["tokenId"];
            $prices = $node["orderData"]["bestAskV2"]["priceType"];
            $eth = sprintf("%.3f", $prices["eth"]);
            $usdt = sprintf("%.3f", $prices["usd"]);
            $ronin = sprintf("%.3f", $usdt/$this->rate["ron"]["usd"]);

            // $key = "{$prifix}_{$id}";
            // array_push($this->uncompleted_ids, $key);
            array_push($items, $this->getItem($id, "origin", $eth, $usdt, $ronin, -1));
        }
        return $items;
    }

    public function getOriginBallInfo($planet_id)
    {
        $path = str_replace("\\", "/", dirname(__FILE__));
        exec("node {$path}/Opensea.js getOriginBallInfo {$planet_id}", $response);
        $responseArray = json_decode($response[0], true);
        $rows = isset($responseArray["data"]["nft"]['traits']["edges"]) ? $responseArray["data"]["nft"]['traits']["edges"] : [];
        if (empty($rows)) {
            return null;
        }
        $values = [];
        $propertys = ["Element-Air","Element-Water","Element-Fire","Element-Earth","Conjunction Count"];
        foreach ($propertys as $property) {
            foreach ($rows as $row) {
                $node = $row["node"];
                if ($node["traitType"] ==  $property) {
                    array_push($values, $node["floatValue"]);
                    break;
                }
            }
        }

        if (count($values)!= 5) {
            return null;
        }
        @list($air, $water, $fire, $earth, $breedCount) = $values;
        $age = -1;
        return Attributes::new($age, $earth, $fire, $water, $air, $breedCount);
    }


    public function getDerivedBalls()
    {
        $path = str_replace("\\", "/", dirname(__FILE__));
        exec("node {$path}/Opensea.js getDerivedBalls", $response);
        $responseArray = json_decode($response[0], true);

        $balls = isset($responseArray["data"]["collectionItems"]["edges"]) ? $responseArray["data"]["collectionItems"]["edges"] : [];
        $items = [];
        foreach ($balls as $item) {
            $node = $item["node"];
            $id = $node["tokenId"];
            $askPrice = $node["orderData"]["bestAskV2"];
            $symbol = $askPrice["perUnitPriceType"]["symbol"];
            $price = $askPrice["perUnitPriceType"]["unit"];
            $usdt = $askPrice["perUnitPriceType"]["usd"];
            switch ($symbol) {
                case "ETH":
                    $eth = $price;
                    $ronin = $usdt/$this->rate["ron"]["usd"];

                    break;
                default:
                    $ronin = $price;
                    $eth = $usdt/$this->rate["eth"]["usd"];
            }

            $breedCount = -1;
            $name = $node["name"];
            preg_match("/[\w]+/", $item["name"], $name);
            $name = isset($name[0]) ? $name[0] : '';
            array_push($items, $this->getItem($id, "derived", $eth, $usdt, $ronin, $breedCount, $name));
        }

        return $items;
    }

    public function setSrc()
    {
        $this->src = "opensea.io";
    }
}
