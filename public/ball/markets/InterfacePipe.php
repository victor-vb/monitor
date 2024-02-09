<?php
namespace Ball\Markets;
interface InterfacePipe {
    public function marketNTF();
    public function read();
    public static function loopRun();
}

