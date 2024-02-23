<?php

namespace App\Service\Ball;

class Apeironnft
{
    public $balls = [];

    public static $instance = null;

    public $messages = [];

    public static function getInstance()
    {
        if (!static::$instance) {
            static::$instance = new Apeironnft();
            static::$instance->initOriginNFT();
            static::$instance->initDerivedNFT();
        }
        return static::$instance;
    }

    public function makeIteraor($start, $end, $name, $maxPrice, $prifix)
    {
        for ($id = $start; $id < $end; $id++) {
            $key = "{$prifix}_{$id}";
            static::getInstance()->balls[$key] = new Ball($id, $prifix, $name, $maxPrice);
        }
    }

    public function checkBall($id, $prifix):bool{
        $key = "{$prifix}_{$id}";
        if(!isset(static::getInstance()->balls[$key])){
            return false;
        }else{
            return true;
        }
    }

    public function getBall($id, $prifix): Ball
    {
        $key = "{$prifix}_{$id}";
        return static::getInstance()->balls[$key];
    }

    public function setBall(Ball $ball)
    {
        $key = "{$ball->prifix}_{$ball->id}";

        static::getInstance()->balls[$key] = $ball;
    }

    public function initOriginNFT()
    {
        $this->makeIteraor(0, 14, "Primal", 7, "origin");
        $this->makeIteraor(14, 387, "Divine", 2.5, "origin");
        $this->makeIteraor(387, 1133, "Arcane", 0.73, "origin");
        $this->makeIteraor(1133, 3188, "Mythic", 0.37, "origin");
    }

    public function initDerivedNFT()
    {
        $this->makeIteraor(0, 5, "T1", 32.4, "derived");
        $this->makeIteraor(5, 23, "T2", 21, "derived");
        $this->makeIteraor(23, 128, "T3", 16.2, "derived");
        $this->makeIteraor(128, 362, "T4", 10.8, "derived");
        $this->makeIteraor(362, 856, "T5", 7.2, "derived");

        $this->makeIteraor(856, 1594, "T6", 5.4, "derived");
        $this->makeIteraor(1594, 2752, "T7", 3.6, "derived");
        $this->makeIteraor(2752, 4291, "T8", 2.4, "derived");
        $this->makeIteraor(4291, 6449, "T9", 1.8, "derived");
        // $this->makeIteraor(4291, 6449, "T9", 3, "derived");

        $this->makeIteraor(6449, 8862, "T10", 1.2, "derived");

        $this->makeIteraor(8862, 11498, "T11", 0.72, "derived");
    }

    public function checkPrice()
    {
        foreach ($this->balls as $id=>$ball) {
            if ($ball->getBallMaxprice()) {
                array_push($this->messages, $ball->toString());
            }
            $ball->canBeSendMessage = false;
        }
    }
}
