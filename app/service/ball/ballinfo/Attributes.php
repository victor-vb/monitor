<?php

namespace App\Service\Ball\BallInfo;

class Attributes
{
    public $age=0;
    public $fire=0;
    public $earth=0;
    public $air=0;
    public $water=0;
    public $breedCount=0;

    public static function new($age, $earth, $fire, $water, $air, $breedCount)
    {
        $attr = new Attributes();
        $attr->age = $age;
        $attr->earth = $earth;
        $attr->fire = $fire;
        $attr->water = $water;
        $attr->air = $air;
        $attr->breedCount = $breedCount;
        return $attr;
    }

    public function toArray()
    {
        $data = [
            "age"=> $this->age,
            "earth"=> $this->earth,
            "fire"=> $this->fire,
            "water"=> $this->water,
            "air"=> $this->air,
            "breedCount"=> $this->breedCount,
        ];

        return $data;
    }

    public function tojson()
    {
        return json_encode($this->toArray());
    }


    public static function from($data)
    {
        if (is_string($data)) {
            $attrArray = json_decode($data, true);
        } else {
            $attrArray = $data;
        }
        if (
            !isset($attrArray['age']) ||
            !isset($attrArray['earth']) ||
            !isset($attrArray['fire']) ||
            !isset($attrArray['water']) ||
            !isset($attrArray['air']) ||
            !isset($attrArray['breedCount'])
        ) {
            return null;
        }

        return Attributes::new(
            $attrArray['age'],
            $attrArray['earth'],
            $attrArray['fire'],
            $attrArray['water'],
            $attrArray['air'],
            $attrArray['breedCount']
        );
    }

    public function checkAttributes()
    {
        $attrs = 0;
        if ($this->fire > 0) {
            $attrs++;
        }

        if ($this->earth > 0) {
            $attrs++;
        }

        if ($this->air > 0) {
            $attrs++;
        }

        if ($this->water > 0) {
            $attrs++;
        }
        return $attrs;
    }

    public function toString()
    {
        $attr = "";
        if ($this->fire > 0) {
            $attr .="fire:{$this->fire}% ";
        }

        if ($this->earth > 0) {
            $attr .="earth:{$this->earth}% ";
        }

        if ($this->air > 0) {
            $attr .="air:{$this->air}% ";
        }

        if ($this->water > 0) {
            $attr .="water:{$this->water}% ";
        }


        if ($this->age > 0) {
            $attr .="age:{$this->age}";
        }

        return trim($attr);
    }
}
