<?php
namespace Ball\Markets;
abstract class AbstractPipe implements InterfacePipe{

    public function read()
    {
        $message = fgets(STDIN);
        $message = rtrim($message, "\n\r");
        return true;
    }

    public static function loopRun(){
        $markets = new static();
        for(;;){
            $count = $markets->read();
            $data = $markets->marketNTF($count);
            echo($data.PHP_EOL);
        }
    }
}