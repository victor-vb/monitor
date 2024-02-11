<?php
namespace App\Service\Ball;
class Ball
{
    public $id;
    public $enname;
    public $reproducecCount;
    public $maxPriceEth;
    public $nowPriceEth;
    public $nowPriceUsdt;
    public $src;
    public $canBeSendMessage = false;

    public function __construct($id, $enname='', $maxPriceEth=0)
    {
        $this->id = $id;
        $this->enname = $enname;
        $this->maxPriceEth = $maxPriceEth;
    }

    public function setReproducecCount($reproducecCount): Ball
    {
        $this->reproducecCount = $reproducecCount;
        return $this;
    }

    public function setNowPrice($nowPriceEth,$nowPriceUsdt=0): Ball
    {
        if($this->nowPriceEth != $nowPriceEth){
            $this->canBeSendMessage = true;
        }
        $this->nowPriceEth = $nowPriceEth;
        $this->nowPriceUsdt = $nowPriceUsdt;
        return $this;
    }

    public function setSrc($src): Ball
    {
        $this->src = $src;
        return $this;
    }


    public function getBallMaxprice(): bool
    {
        if(floatval($this->nowPriceEth) == 0){
            return false;
        }
        if ($this->maxPriceEth > $this->nowPriceEth) {
            return true;
        } else {
            return false;
        }
    }

    public function toString()
    {
        $string = sprintf(
            "资源id:%s \n球类型:%s \n繁殖次数:%s \n现价ETH:%sE \n现价USDT:%.2fU \n挂单来源:%s\n",
            $this->id,
            $this->enname,
            $this->reproducecCount,
            $this->nowPriceEth,
            $this->nowPriceUsdt,
            $this->src
        );

        return $string;
    }
}