<?php
namespace Ball\Markets;
use Tools;

class Opensea extends AbstractPipe
{

    public function marketNTF()
    {
        $path = str_replace("\\","/",dirname(__FILE__));
        exec("node {$path}/Opensea.js",$response);
        $responseArray = json_decode($response[0],true);
        $balls = isset($responseArray["data"]["collectionItems"]["edges"]) ? $responseArray["data"]["collectionItems"]["edges"] : [];
        $items = [];
        foreach($balls as $ball){
            $node = $ball["node"];
            $id = $node["tokenId"];
            $prices = $node["orderData"]["bestAskV2"]["priceType"];
            $eth = sprintf("%.3f", $prices["eth"]);
            $usdt = sprintf("%.3f",$prices["usd"]);
            array_push($items, [
                "id"=>$id,
                "eth"=>$eth,
                "usdt"=>$usdt,
                "breedCount"=>-1,
            ]);

        }
        $data = [
            "items"=>$items,
            "src"=>"https://opensea.io/"
        ];
        return json_encode($data, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }
}

