<?php
define("DIR",str_replace("\\","/",dirname(__FILE__)).'/');
ini_set('date.timezone', 'Asia/Shanghai');

set_error_handler(function( int $errno,string $errstr,string $errfile ,int $errline){
   $error = "{$errfile} {$errline} {$errno}\n{$errstr}\r\n\r\n";
   file_put_contents(DIR."error.log",$error);
   return true;
});

spl_autoload_register(function($file){
   $file = str_replace("\\","/",$file);
   $dir = strtolower(dirname($file));
   $filename = basename($file);
   $file = DIR."{$dir}/{$filename}.php";
   include_once $file;
},false);



