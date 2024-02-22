<?php
namespace App\Logic;
class Exception{
    public function handle($errors){
        foreach($errors as $error){
            echo sprintf("程序执行错误:%s",$error["data"]).PHP_EOL;
        }
        
    }
}