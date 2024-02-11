<?php

include_once "../autoload.php";
use App\Task\Tasks;
use App\Task\Process;
use App\Task\Protocol;

$call = function () {
    // $protocol = Protocol::new("Ball", "handle");
    // $tasks = Tasks::instance("BallTask", 'App\Ball\Markets')->setWaitAll(false)->setProtocol($protocol);
    // $tasks->setCallClassName("Office")->add();
    // $tasks->setCallClassName("Skymavis")->add();
    // $tasks->setCallClassName("Opensea")->add();

    $protocol = Protocol::new("Chain", "handle");
    $tasks = Tasks::instance("ChainTask", 'App\Service\Chain')->setProtocol($protocol);
    $tasks->setCallClassName("Chain")->add();


    return $tasks;
};

// print_r($call());
(new Process())->run($call);
