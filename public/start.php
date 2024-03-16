<?php

include_once "../autoload.php";
use App\Task\Tasks;
use App\Task\Process;
use App\Task\Protocol;

$call = function () {
    $protocol = Protocol::new("Ball","handle");
    $tasks = Tasks::instance("BallTask",'App\Service\Ball\Markets')->setWaitAll(true)->setProtocol($protocol);
    $tasks->setCallClassName("Office")->add();
    $tasks->setCallClassName("Skymavis")->add();
    // $tasks->setCallClassName("Opensea")->add();

    // $protocol = Protocol::new("Sol", "handle");
    // $tasks = Tasks::instance("SolTask", 'App\Service\Sol')->setProtocol($protocol);
    // $tasks->setCallClassName("Test")->add();


    return $tasks;
};

// print_r($call());
(new Process())->run($call);