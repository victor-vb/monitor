<?php
include_once "./autoload.php";
use ball\Apeironnft;
class Process{
    public $desc = array(
        0 => array("pipe", "r"),    // stdin is a pipe that the child will read from
        1 => array("pipe", "w"),    // stdout is a pipe that the child will write to
        2 => array("pipe", "w")     // stderr is a file to write to
    );

    public $commands = [
        "Office"=>"php Task.php -c Ball\Markets\Office",
        "Skymavis"=>"php Task.php -c Ball\Markets\Skymavis",
        "Opensea"=>"php Task.php -c Ball\Markets\Opensea",
    ];

    public $pipes = [];

    public $processes = [];

    public function run(){
        $this->createProcess();
        for(;;){
            $data = [];
            foreach($this->pipes as $market=>$pipes){
                list($stdin,$stdout,$stderr) =  $pipes;
                stream_set_blocking($stdin, false);
                fwrite($stdin, "\r\n");
            }
            $count = 0;
            while(true){
                foreach($this->pipes as $market=>$pipes){
                    list($stdin,$stdout,$stderr) =  $pipes;
                    stream_set_blocking($stdout, false);
                    $content = fgets($stdout);
                    
                    if ($content === false) {
                        usleep(100);
                        continue;
                    }else{
                        $count++;
                        file_put_contents(DIR."log.txt","{$market} \n{$content}".PHP_EOL,FILE_APPEND);
                        $data[$market] = json_decode($content,true);
                    }
                }
                if($count >= count($this->processes)){
                    break;
                }
            }
            // exit;
            self::handle($data);
            sleep(15);
        }
    }


    public function createProcess(){
        $options = ["bypass_shell"=>true];
        $options = [];
        foreach($this->commands as $market=>$command){
            $process = proc_open($command, $this->desc, $pipes,DIR.'console/',getenv(),$options);
            if (is_resource($process)) {
                $this->pipes[$market] = $pipes;
                $this->processes[$market] =  $process;
            }
        }
    }

    public static function handle($markets)
    {
        $balls = [];
        $count = [];
        foreach($markets as $market=>$items){  
            $rows = isset($items["items"]) ? $items["items"] : [];
            $count[$market] = count($rows);
            $src = isset($items["src"]) ? $items["src"] : '';
            foreach($rows as $row){
                $row["src"] = $src;
                if(isset($balls[$row["id"]])){
                    $other = $balls[$row["id"]];
                    if($row["eth"] < $other["eth"]){
                        $balls[$row["id"]] = $row;
                    }
                }else{
                    $balls[$row["id"]] = $row;
                }
            }
        }
        $Apeironnft = Apeironnft::getInstance();
        foreach($balls as $ball){
            $id= $ball["id"];
            if ($id > count($Apeironnft->balls)) {
                continue;
            }
            if(!$id){
                // print_r($ball);
                continue;
            }

            $eth = $ball["eth"];
            $usdt = $ball["usdt"];
            $breedCount = $ball["breedCount"];
            $src = $ball["src"];
            $ball = $Apeironnft->getBall($id)
            ->setNowPrice($eth,$usdt)
            ->setReproducecCount($breedCount)
            ->setSrc($src);
            $Apeironnft->setBall($id, $ball);
        }
        $Apeironnft->checkPrice();
        $messages = implode(PHP_EOL,$Apeironnft->messages);
        if($messages){
            Tools::sendMessage($messages);
            $Apeironnft->messages = [];
        }
        echo sprintf("市场情况:%s,监控时间:%s".PHP_EOL,json_encode($count,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),date("Y-m-d H:i:s"));
    }
}

(new Process())->run();