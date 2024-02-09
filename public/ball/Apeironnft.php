<?php
namespace ball;
class Apeironnft
{
    public $balls = [];

    public static $instance = null;

    public $messages = [];

    public static function getInstance()
    {
        if (!static::$instance) {
            static::$instance = new Apeironnft();
            static::$instance->initNFT();
        }
        return static::$instance;
    }

    public function makeIteraor($start, $end, $name, $maxPrice)
    {
        for ($id = $start; $id < $end; $id++) {
            array_push(static::getInstance()->balls, new Ball($id, $name, $maxPrice));
        }
    }

    public function getBall($id): Ball
    {
        return static::getInstance()->balls[$id];
    }

    public function setBall($id, Ball $ball)
    {
        static::getInstance()->balls[$id] = $ball;
    }

    public function initNFT()
    {
        $this->makeIteraor(0, 14, "Primal", 7);
        $this->makeIteraor(14, 387, "Divine", 2.5);
        $this->makeIteraor(387, 1133, "Arcane", 0.73);
        $this->makeIteraor(1133, 3188, "Mythic", 0.37);
    }

    public function checkPrice(){
        foreach($this->balls as $id=>$ball){
            if(!$ball->canBeSendMessage){
                continue;
            }
            if($ball->getBallMaxprice()){
               array_push($this->messages,$ball->toString());
            }
            $ball->canBeSendMessage = false;
        }
    }
}