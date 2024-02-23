<?php

namespace App\Service\Ball\Markets;

use App\Task\AbstractProcess;
use App\Task\Protocol;
use App\Lib\Tools;
abstract class AbstractBall extends AbstractProcess implements InterfaceBall
{
    protected $cahcefile = DIR."rate.txt";

    protected $rate;

    protected $src;
   
    public function before()
    {
        $this->setSrc();
        $this->rate = $this->getRate();
    }
    public function main()
    {
        $nft = $this->marketNTF();
        return $nft;
    }

    public function after()
    {
        sleep(15);
    }

    public function getItem($id, $prifix, $eth, $usdt, $ronin,$breedCount,$name="")
    {
        return [
            "id"=>$id,
            "eth"=>sprintf("%.3f", $eth),
            "usdt"=>sprintf("%.3f", $usdt),
            "ronin"=>sprintf("%.3f", $ronin),
            "breedCount"=>$breedCount,
            "name"=>$name,
            "prifix"=>$prifix
         ];
    }

    /**
     * 使用Skymavis市场获取当前各自代币汇率
     * @param integer $count
     * @return void
     */
    public function getRate($count=0)
    {
        // curl 'https://marketplace-graphql.skymavis.com/graphql' \
        // -H 'content-type: application/json' \
        // -H 'origin: https://marketplace.skymavis.com' \
        // -H 'referer: https://marketplace.skymavis.com/' \
        // -H 'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36' \
        // --data-raw '{"operationName":"NewEthExchangeRate","variables":{},"query":"query NewEthExchangeRate {\n  exchangeRate {\n    eth {\n      usd\n      __typename\n    }\n    slp {\n      usd\n      __typename\n    }\n    ron {\n      usd\n      __typename\n    }\n    axs {\n      usd\n      __typename\n    }\n    usd {\n      usd\n      __typename\n    }\n    __typename\n  }\n}\n"}' \
        // --compressed
        $rate = $this->getCacheRate();
        if ($rate) {
            return $rate;
        }
        $api = "https://marketplace-graphql.skymavis.com/graphql";

        $headers = [
            "content-type: application/json",
            "origin: https://marketplace.skymavis.com",
            "referer: https://marketplace.skymavis.com/",
            "user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36"
        ];
        $row =<<<Row
{"operationName":"NewEthExchangeRate","variables":{},"query":"query NewEthExchangeRate {\\n  exchangeRate {\\n    eth {\\n      usd\\n      __typename\\n    }\\n    slp {\\n      usd\\n      __typename\\n    }\\n    ron {\\n      usd\\n      __typename\\n    }\\n    axs {\\n      usd\\n      __typename\\n    }\\n    usd {\\n      usd\\n      __typename\\n    }\\n    __typename\\n  }\\n}\\n"}
Row;
        $response = Tools::curl($api, $row, 1, $headers);
        $responseArray = json_decode($response, true);

        $rate = isset($responseArray["data"]["exchangeRate"]) ? $responseArray["data"]["exchangeRate"] : [];

        if (!$rate && $count <=3) {
            $count++;
            return $this->getRate($count);
        }
        $this->setChcheRate($rate);

        return $rate;
    }

    public function setChcheRate($rate)
    {
        $time = time();
        $rate = [
            "timestamp"=>$time,
            "datetime"=>date("Y-m-d H:i:s", $time),
            "rate"=>$rate
        ];

        file_put_contents($this->cahcefile, json_encode($rate, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
        return true;
    }

    public function getCacheRate()
    {
        $time = time();
        $rate = json_decode(file_get_contents($this->cahcefile), true);
        $rate = $rate ? $rate : [];
        // 缓存5分钟
        if (isset($rate['timestamp']) && $time-$rate["timestamp"] < 3*60) {
            return $rate["rate"];
        } else {
            return [];
        }
    }
}
