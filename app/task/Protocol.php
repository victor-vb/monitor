<?php
namespace App\Task;

class Protocol{
    public $namespace = 'App\Logic';
    
    public $callbackClass;

    public $callbackMethod;

    public $data = [];

    public static function new($callbackClass,$callbackMethod,$data = []){
        $protocol = new Protocol();
        $protocol->callbackClass = $callbackClass;
        $protocol->callbackMethod = $callbackMethod;
        $protocol->data = $data;
        return $protocol;
    }

    public static function from($protocolString){
        $protocol = new Protocol();
        return $protocol->unserialize($protocolString);
    }

    public function setData($data){
        $this->data = $data;
        return $this;
    }

    public function serialize():String
    {
        $reflection = new \ReflectionClass($this);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
        $data = [];
        foreach($properties as $property){
            $name = $property->getName();
            $data[$name] = $this->$name;
        }
        return json_encode($data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).PHP_EOL;
    }

    public function unserialize($protocolString):Protocol
    {
        if(!is_string($protocolString)){
            return $this;
        }
        $data = json_decode(trim($protocolString),true);
        foreach($data as $property=>$value){
            $this->$property = $value;
        }
        return $this;
    }

    public function join($other,...$more){
        $data = [$other->data];
        foreach($more as $row){
            array_push($data,$row->data);
        }
        array_push($this->data,...$data);
        return $this;
    }

    public function call(){
        $className = $this->namespace.'\\'.$this->callbackClass;
        $caller = new $className();
        if(!method_exists($caller,$this->callbackMethod)){
            throw new \Exception("回调函数不存在");
        }
        call_user_func_array([$caller,$this->callbackMethod],[$this->data]);
    }
}