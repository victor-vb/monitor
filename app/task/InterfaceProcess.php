<?php

namespace App\Task;

interface InterfaceProcess
{

    public function read();

    public static function loopRun();

    public function before();
    public function main();
    public function after();
}
