<?php

namespace App\Service\Ball\Markets;

use App\Task\AbstractProcess;
use App\Task\Protocol;

abstract class AbstractBall extends AbstractProcess implements InterfaceBall
{
    public function main()
    {
        $nft = $this->marketNTF();
        return $nft;
    }

    public function after()
    {
        sleep(15);
    }
}
