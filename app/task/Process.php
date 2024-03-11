<?php

namespace App\Task;

class Process
{
    public $desc = array(
        0 => array("pipe", "r"),    // stdin is a pipe that the child will read from
        1 => array("pipe", "w"),    // stdout is a pipe that the child will write to
        2 => array("pipe", "w")     // stderr is a file to write to
    );


    public static $groups = [];

    public function run(callable $fun)
    {
        $tasks = $fun();
        $processes = $tasks->getTasks();

        $this->process($processes);

        foreach (self::$groups as $callProcessName=>$process) {
            foreach ($process["pipes"] as $pipes) {
                list($stdin, $stdout, $stderr) =  $pipes;
                $protlcol = $tasks->getProtlcol($callProcessName);
                fwrite($stdin, $protlcol->serialize());
            }
        }


        for (;;) {
            foreach (self::$groups as $callProcessName=>$process) {
                foreach ($process["pipes"] as $pipes) {
                    list($stdin, $stdout, $stderr) =  $pipes;
                    stream_set_blocking($stdout, false);
                    $content = fgets($stdout);

                    if ($content === false) {
                        usleep(100);
                        continue;
                    } else {
                        $protlcol = $tasks->getProtlcol($callProcessName);
                        $protlcol->join(Protocol::from($content));
                        if ($tasks->isWaitAll($callProcessName)) {
                            if (count($protlcol->data) == count($processes[$callProcessName]["process"])) {
                                $protlcol->call();
                                $protlcol->setData([]);
                            }
                        } else {
                            $protlcol->call();
                            $protlcol->setData([]);
                        }
                    }
                }
            }
            usleep(100);
        }
    }


    public function process($commands)
    {
        $options = [];
        foreach ($commands as $callProcessName => $rows) {
            foreach ($rows["process"] as $command) {
                $process = proc_open($command, $this->desc, $pipes, DIR, getenv(), $options);
                if (is_resource($process)) {
                    self::$groups[$callProcessName]["process"][] =  $process;
                    self::$groups[$callProcessName]["pipes"][] = $pipes;
                    self::$groups[$callProcessName]["data"] = [];
                }
            }
        }

    }
}
