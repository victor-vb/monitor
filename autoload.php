<?php
define("DIR",str_replace("\\","/",dirname(__FILE__)).'/');

spl_autoload_register(function($file){
   $file = str_replace("\\","/",$file);
   $dir = strtolower(dirname($file));
   $filename = basename($file);
   $file = DIR."{$dir}/{$filename}.php";
   include_once $file;
},false);



