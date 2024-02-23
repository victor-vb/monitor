<?php

namespace App\Service\Ball;

class Ball
{
    public $id;
    public $prifix;
    public $enname;
    public $reproducecCount;
    /**
     * 最大受支持的价格
     *
     * @var float
     */
    public $maxPriceEth;

    /**
    * 最后一次网络请求价格
    * @var float
    */
    public $nowPriceEth;
    public $nowPriceUsdt;
    public $nowPriceRon;

    /**
     * 最后一次的网络请求价格，比较以后的价格，缓存到此为止
     * @var float
     */
    public $lastPriceEth = 0;
    public $lastPriceUsdt = 0;
    public $lastPriceRon = 0;

    public $src;
    public $name;

    public function __construct($id, $prifix, $enname='', $maxPriceEth=0)
    {
        $this->id = $id;
        $this->enname = $enname;
        $this->maxPriceEth = $maxPriceEth;
        $this->prifix = $prifix;
    }

    public function setReproducecCount($reproducecCount): Ball
    {
        $this->reproducecCount = $reproducecCount;
        return $this;
    }

    public function setNowPrice($nowPriceEth, $nowPriceUsdt=0, $nowPriceRon=0): Ball
    {
        $this->nowPriceRon = $nowPriceRon;
        $this->nowPriceEth = $nowPriceEth;
        $this->nowPriceUsdt = $nowPriceUsdt;
        return $this;
    }

    public function setSrc($src): Ball
    {
        $this->src = $src;
        return $this;
    }

    public function setName($name): Ball
    {
        $this->name = $name;
        return $this;
    }


    public function getBallMaxprice(): bool
    {
        if (floatval($this->nowPriceEth) == 0) {
            return false;
        }

        
        /**
         * 当eth和ron两个币种都有变动，说明此商品是上架或者重新上架的操作，则更新所有价格
         */
        if ($this->lastPriceRon != $this->nowPriceRon && $this->lastPriceEth != $this->nowPriceEth) {
            $this->lastPriceEth = $this->nowPriceEth;
            $this->lastPriceRon = $this->nowPriceRon;

            /**
             * 如果系统上一次usdt价格不等于零，说明此商品上架过
             * 需要重新置为零，以便eth价格符合标准直接走通知分支
             */
            if ($this->lastPriceUsdt != 0) {
                $this->lastPriceUsdt = 0;
            }
        }

        $checked = false;
        if ($this->maxPriceEth >= $this->nowPriceEth) {
            if ($this->lastPriceUsdt > 0) {
                /**
                 * 如果当前eth的价格小于设定的价格，但是不是首次监听，存在最后一次的价格
                 * 那么需要满足，上一次的usdt价格减去当前的usdt价格，价格相差5%，则进行触发通知
                 * 上一次ustd价格 - 当前usdt价格 大于零
                 *  说明因为汇率原因，可以以便宜的价格买到此币种
                 */
                $diff = $this->lastPriceUsdt - $this->nowPriceUsdt;
                if ($diff>0 && $diff/$this->lastPriceUsdt >= 0.05) {
                    // 更新本次的usdt价格
                    $this->lastPriceUsdt = $this->nowPriceUsdt;
                    $checked = true;
                }
            } else {
                // 如果当前eth的价格小于设定的价格，并且是首次监听，则认定她是满足条件
                $checked = true;
                $this->lastPriceUsdt = $this->nowPriceUsdt;
            }
        }

        return $checked;
    }

    public function toString()
    {
        $string = sprintf(
            "资源id:%s \n球类型:%s \n繁殖次数:%s \n现价:%sE %sU %sR \n触发价:%sE \n挂单来源:%s\n",
            $this->id,
            $this->enname,
            $this->reproducecCount,
            $this->nowPriceEth,
            $this->nowPriceUsdt,
            $this->nowPriceRon,
            $this->maxPriceEth,
            $this->src
        );

        return $string;
    }
}
