<?php
include_once "./autoload.php";
class Task{
    
    public $method = "loopRun";

    public static function Run(){
        $getopt = getopt("c:");
        if(!isset($getopt["c"])){
            echo "操作错误！".PHP_EOL;
            return false;
        }
        $class = $getopt["c"];
        $task = new static();
        $method =  $task->method;
        call_user_func("{$class}::$method");
    }
}

Task::Run();