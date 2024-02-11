<?php
namespace App\Service\Chain;
use App\Task\AbstractProcess;

class Chain extends AbstractProcess{
    
    public function main(){

        $rand = rand(1000,1000000);
        $data= [
            "data"=>"heart",
            "rand"=>$rand
        ];
        return $data;
    }

    
    public function after()
    {
        sleep(3);
    }
}