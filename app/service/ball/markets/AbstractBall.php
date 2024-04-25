<?php

namespace App\Service\Ball\Markets;

use App\Task\AbstractProcess;
use App\Lib\Tools;
use App\Service\Ball\BallInfo\Attributes;

abstract class AbstractBall extends AbstractProcess implements InterfaceBall
{
    protected $cahcefile = DIR."rate.txt";

    protected $cacheAttrFile  = DIR."attrs.txt";

    protected $rate;

    protected $src;

    protected $before_time;

    public const MAX_SLEEP_TIME = 5;

    // completed
    protected $completed_ids = [

    ];

    protected $uncompleted_ids = [];

    public function before()
    {
        $this->before_time = time();
        $this->setSrc();
        $this->rate = $this->getRate();
        $this->completed_ids = $this->loadAttrs();
    }
    public function main()
    {
        $nft = $this->marketNTF();
        return $nft;
    }

    public function after()
    {
        $ids = [];
        foreach ($this->uncompleted_ids as $id) {
            if (!in_array($id, array_keys($this->completed_ids))) {
                array_push($ids, $id);
            }
        }
        foreach ($ids as $id) {
            $idinfo = explode("_", $id, 2);
            list($_, $planetID) = $idinfo;
            $method = "getOriginBallInfo";
            if (method_exists($this, $method)) {
                $attr = $this->$method($planetID);
                if ($attr instanceof Attributes) {
                    $this->completed_ids[$id] = $attr;
                }
                if (time()-$this->before_time >= static::MAX_SLEEP_TIME-1) {
                    break;
                }
            }
        }
        // $time = time()-$this->before_time;
        // if ($time < static::MAX_SLEEP_TIME) {
        //    sleep(static::MAX_SLEEP_TIME-$time);
        // }
        // $this->uncompleted_ids = [];
        // $this->saveAttrs();
        sleep(static::MAX_SLEEP_TIME);
    }

    public function getBallInfo()
    {
        $ids = [];
        foreach ($this->uncompleted_ids as $id) {
            if (!in_array($id, array_keys($this->completed_ids))) {
                array_push($ids, $id);
            }
        }
        foreach ($ids as $id) {
            $idinfo = explode("_", $id, 2);
            list($_, $planetID) = $idinfo;
            $method = "getOriginBallInfo";
            if (method_exists($this, $method)) {
                $attr = $this->$method($planetID);
                if ($attr instanceof Attributes) {
                    $this->completed_ids[$id] = $attr;
                }
                if (time()-$this->before_time >= static::MAX_SLEEP_TIME-1) {
                    break;
                }
            }
        }
        $this->uncompleted_ids = [];
        $this->saveAttrs();
    }

    public function getItem($id, $prifix, $eth, $usdt, $ronin, $breedCount, $name="")
    {
        $key = "{$prifix}_{$id}";
        $attrs = isset($this->completed_ids[$key]) ? $this->completed_ids[$key] : "";
        $item =  [
            "id"=>$id,
            "eth"=>sprintf("%.3f", $eth),
            "usdt"=>sprintf("%.3f", $usdt),
            "ronin"=>sprintf("%.3f", $ronin),
            "breedCount"=>$breedCount,
            "name"=>$name,
            "prifix"=>$prifix,
            "attrs"=>$attrs instanceof Attributes ? $attrs->toArray() : []
         ];
        return $item;
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

    public function loadAttrs()
    {
        $attrs = [];
        if (file_exists($this->cacheAttrFile)) {
            $cached = json_decode(file_get_contents($this->cacheAttrFile), true);
            if ($cached) {
                foreach ($cached as $complexid => $array) {
                    $attr = Attributes::from($array);
                    if ($attr instanceof Attributes) {
                        $attrs[$complexid]  = $attr;
                    }
                }
            }
        }
        return $attrs;
    }

    public function getAttr($attrs, $planet_id, $prifix="origin")
    {
        $complexid = "{$prifix}_{$planet_id}";
        if (isset($attrs[$complexid])) {
            return $attrs[$complexid];
        } else {
            return null;
        }
    }

    public function saveAttrs($extra = [])
    {
        $attrs = [];
        $rows  = array_merge($this->completed_ids, $extra);
        foreach ($rows as $complexid=>$attr) {
            if ($attr instanceof Attributes) {
                $attrs[$complexid] = $attr->toArray();
            }
        }
        $attrs = array_merge($attrs, $this->loadAttrs());
        file_put_contents($this->cacheAttrFile, json_encode($attrs), LOCK_EX);
    }
}
