<?php

use App\Task\Protocol;

include_once "./autoload.php";
class Task{
    
    public static $method = "loopRun";

    public static function Run(){
        $getopt = getopt("c:");
        $error = [
            "process"=>"\Task",
            "data"=>"操作错误！"
        ];
        if(!isset($getopt["c"])){
            echo Protocol::new("Exception","handle",$error)->serialize();
            return false;
        }
        $class = trim($getopt["c"],"'\"");
        // $class = $getopt["c"];
        $method =  self::$method;
        if (!class_exists($class)){
            $error["data"] = "{$class}不存在!";
            echo Protocol::new("Exception","handle",$error)->serialize();
            return false;
        }
        call_user_func("{$class}::$method");
    }
}

Task::Run();