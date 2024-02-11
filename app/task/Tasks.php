<?php

namespace App\Task;

class Tasks
{
    public $commands = [];

    public $protocol = [];

    public $command;

    public $callNamespace;

    public $callClassName;

    public $callProcessName;

    public $waitAll = false;

    public static $instance = null;

    public function __construct()
    {
        $this->command = "php ".DIR."Command";
    }

    public static function instance($callProcessName, $callNamespace="")
    {
        if (!self::$instance) {
            self::$instance = new static();
        }
        $tasks = self::$instance;

        $tasks->callProcessName = $callProcessName;
        $tasks->callNamespace = $callNamespace;

        return $tasks;
    }

    public function setProtocol($protocol)
    {
        $this->protocol[$this->callProcessName] = $protocol;

        return $this;
    }

    public function setWaitAll($flag=false){
        $this->waitAll = $flag;
         return $this;

    }



    public function setCallClassName($callClassName)
    {
        $this->callClassName = $callClassName;
        return $this;
    }


    public function add()
    {
        $classname = "'{$this->callNamespace}\\{$this->callClassName}'";
        $classname = trim($classname,"\\");
        $this->commands[$this->callProcessName]["waitAll"] = $this->waitAll;
        $this->commands[$this->callProcessName]["process"][] = "{$this->command} -c {$classname}";
        return $this;
    }

    public function getTasks()
    {
        return $this->commands;
    }

    public function getProtlcol($callProcessName){
        if(!isset($this->protocol[$callProcessName])){
            throw new \Exception("未设置通讯协议！");
        }
        return $this->protocol[$callProcessName];
    }

    public function isWaitAll($callProcessName){
        return $this->commands[$callProcessName]["waitAll"];
    }
}
