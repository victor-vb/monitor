<?php
namespace App\Task;
abstract class AbstractProcess implements InterfaceProcess{

    public $data;

    public function read()
    {
        // stream_set_blocking(STDIN,false);
        $message = fgets(STDIN);
        $message = rtrim($message, "\n\r");
        return $message;
    }

    public static function loopRun(){
        $markets = new static();
        $readline = $markets->read();
        $markets->data = $readline;

        for(;;){
            $markets->before();
            $data = $markets->main();
            $return = [
                "process"=>get_class($markets),
                "data"=>$data
            ];
            $protocol = Protocol::from($data)->setData($return)->serialize();

            echo($protocol);
            $markets->after();
        }
    }


    public function before()
    {
        
    }

    public function after()
    {
        
    }
}