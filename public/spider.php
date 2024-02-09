<?php

class Ball
{
    public $id;
    public $enname;
    public $reproducecCount;
    public $maxPriceEth;
    public $nowPriceEth;
    public $nowPriceUsdt;
    public $src;
    public $canBeSendMessage = false;

    public function __construct($id, $enname='', $maxPriceEth=0)
    {
        $this->id = $id;
        $this->enname = $enname;
        $this->maxPriceEth = $maxPriceEth;
    }

    public function setReproducecCount($reproducecCount): Ball
    {
        $this->reproducecCount = $reproducecCount;
        return $this;
    }

    public function setNowPrice($nowPriceEth,$nowPriceUsdt=0): Ball
    {
        if($this->nowPriceEth != $nowPriceEth){
            $this->canBeSendMessage = true;
        }
        $this->nowPriceEth = $nowPriceEth;
        $this->nowPriceUsdt = $nowPriceUsdt;
        return $this;
    }

    public function setSrc($src): Ball
    {
        $this->src = $src;
        return $this;
    }


    public function getBallMaxprice(): bool
    {
        if(floatval($this->nowPriceEth) == 0){
            return false;
        }
        if ($this->maxPriceEth > $this->nowPriceEth) {
            return true;
        } else {
            return false;
        }
    }

    public function toString()
    {
        $string = sprintf(
            "资源id:%s \n球类型:%s \n繁殖次数:%s \n现价ETH:%sE \n现价USDT:%.2fU \n挂单来源:%s\n",
            $this->id,
            $this->enname,
            $this->reproducecCount,
            $this->nowPriceEth,
            $this->nowPriceUsdt,
            $this->src
        );

        return $string;
    }
}

class Apeironnft
{
    public $balls = [];

    public static $instance = null;

    public $messages = [];

    public static function getInstance()
    {
        if (!static::$instance) {
            static::$instance = new Apeironnft();
            static::$instance->initNFT();
        }
        return static::$instance;
    }

    public function makeIteraor($start, $end, $name, $maxPrice)
    {
        for ($id = $start; $id < $end; $id++) {
            array_push(static::getInstance()->balls, new Ball($id, $name, $maxPrice));
        }
    }

    public function getBall($id): Ball
    {
        return static::getInstance()->balls[$id];
    }

    public function setBall($id, Ball $ball)
    {
        static::getInstance()->balls[$id] = $ball;
    }

    public function initNFT()
    {
        $this->makeIteraor(0, 14, "Primal", 7);
        $this->makeIteraor(14, 387, "Divine", 2.5);
        $this->makeIteraor(387, 1133, "Arcane", 0.73);
        $this->makeIteraor(1133, 3188, "Mythic", 0.37);
    }

    public function checkPrice(){
        foreach($this->balls as $id=>$ball){
            if(!$ball->canBeSendMessage){
                continue;
            }
            if($ball->getBallMaxprice()){
               array_push($this->messages,$ball->toString());
            }
            $ball->canBeSendMessage = false;
        }
    }
}

class Markets
{

    public static $ids = [];

    public static function nftmarkets(){
        self::getNftsmarket();
    }
    /**
     * 官方市场
     *
     * @return void
     */
    public static function getNftsmarket()
    {
        $headers = [
            "content-type: application/json",
            "authority: api-mkt-ronin.apeironnft.com",
            "origin: https://marketplace.apeironnft.com",
            "referer: https://marketplace.apeironnft.com/",
            "user-agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36"
        ];
        $api = "https://api-mkt-ronin.apeironnft.com/v1/graphql";
        $row =<<<Row
{"operationName":"GetPlanets","variables":{"filterInput":{"nftListingStatus":["Listing"]},"pagingInput":{"offset":0,"first":500},"sortInput":{"sortBy":"PriceAsc"}},"query":"query GetPlanets(\$filterInput: GetPlanetsFilterInput\u0021, \$pagingInput: PagingInput, \$sortInput: PlanetSortInput\u0021) {\\n  getPlanets(\\n    filterInput: \$filterInput\\n    pagingInput: \$pagingInput\\n    sortInput: \$sortInput\\n  ) {\\n    edges {\\n      cursor\\n      node {\\n        ...PlanetLite\\n        __typename\\n      }\\n      __typename\\n    }\\n    pageInfo {\\n      ...PageInfo\\n      __typename\\n    }\\n    __typename\\n  }\\n}\\n\\nfragment PlanetLite on Planet {\\n  ...PlanetSuperLite\\n  air\\n  availableAttachTime\\n  bornTime\\n  breedCount\\n  breedCountMax\\n  childrenIDs\\n  coreType\\n  earth\\n  fire\\n  generation\\n  image\\n  lastBreedTime\\n  nftListingStatus\\n  planetCreateTime\\n  planetType\\n  priceInUSD\\n  stage\\n  status\\n  water\\n  parentIDs\\n  listingInfo {\\n    ...ListingInfo\\n    __typename\\n  }\\n  owner {\\n    ...Owner\\n    __typename\\n  }\\n  relicsTokens {\\n    description\\n    id\\n    image\\n    name\\n    thumbnail\\n    __typename\\n  }\\n  __typename\\n}\\n\\nfragment PlanetSuperLite on Planet {\\n  ageDisplay\\n  name\\n  planetID\\n  __typename\\n}\\n\\nfragment ListingInfo on ListingInfo {\\n  currencyCode\\n  duration\\n  endPrice\\n  startDate\\n  startPrice\\n  roninSaleOrder {\\n    kind\\n    orderKindEnum\\n    asset {\\n      erc\\n      addr\\n      id\\n      quantity\\n      __typename\\n    }\\n    expiredAt\\n    paymentToken\\n    startedAt\\n    basePrice\\n    endedAt\\n    endedPrice\\n    expectedState\\n    nonce\\n    marketFeePercentage\\n    signature\\n    orderStatus\\n    __typename\\n  }\\n  __typename\\n}\\n\\nfragment Owner on Account {\\n  avatar\\n  email\\n  name\\n  tag\\n  walletAddress\\n  createdAt\\n  __typename\\n}\\n\\nfragment PageInfo on PageInfo {\\n  ...PageInfoSkipTotal\\n  totalCount\\n  __typename\\n}\\n\\nfragment PageInfoSkipTotal on PageInfo {\\n  ...PageInfoCommon\\n  endCursor\\n  startCursor\\n  __typename\\n}\\n\\nfragment PageInfoCommon on CommonPageInfo {\\n  hasNextPage\\n  hasPreviousPage\\n  __typename\\n}"}
Row;
        $response = self::curl($api, $row, 1, $headers);
        $responseArray = json_decode($response, true);
        if (isset($responseArray["errors"])) {
            return false;
        }
        $balls = isset($responseArray["data"]["getPlanets"]["edges"]) ? $responseArray["data"]["getPlanets"]["edges"] : [];
        $ids = [];
        $Apeironnft = Apeironnft::getInstance();
        foreach ($balls as $item) {
            $node = $item["node"];
            $id = $node["planetID"];

            if ($id > count($Apeironnft->balls)) {
                continue;
            }

            $usdt = $node["priceInUSD"];
            $eth = $node["listingInfo"]["startPrice"];
            $breedCount = $node["breedCount"];
            $ball = $Apeironnft->getBall($id)
            ->setNowPrice($eth,$usdt)
            ->setReproducecCount($breedCount)
            ->setSrc("https://marketplace.apeironnft.com");
            $Apeironnft->setBall($id, $ball);
            array_push($ids, $id);
        }
        sort($ids);
        self::$ids = $ids;
    }

    public static function curl($url, $params = false, $ispost = 0, $headers = [], $timeout = 10)
    {
        $httpInfo = array();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 对认证证书来源的检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // 从证书中检查SSL加密算法是否存在
        if ($headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        if ($ispost) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_URL, $url);
        } else {
            if ($params) {
                if (is_array($params)) {
                    $params = http_build_query($params);
                }
                curl_setopt($ch, CURLOPT_URL, $url . '?' . $params);
            } else {
                curl_setopt($ch, CURLOPT_URL, $url);
            }
        }

        $response = curl_exec($ch);
        if ($response === false) {
            return 'Curl error: ' . curl_error($ch);
            // return false;
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $httpInfo = array_merge($httpInfo, curl_getinfo($ch));
        curl_close($ch);
        return $response;
    }

    public static function sendMessage($text){
        // https://api.telegram.org/bot<token>/getUpdates   获取群组id
        // https://api.telegram.org/bot<token>/sendMessage  发送消息
        $token = "6115387654:AAHelaTW7a6Yn7_19XF-rU1s6eO-ozBoQLA";
        $chat_id = "-4162627115";
        $api = "https://api.telegram.org/bot{$token}/sendMessage";
        $params = [
            "chat_id"=>$chat_id,
            "text"=>$text
        ];
        self::curl($api,$params,1);
    }

    public static function Run()
    {
        for(;;){
            self::nftmarkets();
            Apeironnft::getInstance()->checkPrice();
            $messages = implode(PHP_EOL,Apeironnft::getInstance()->messages);
            if($messages){
                self::sendMessage($messages);
                Apeironnft::getInstance()->messages = [];
            }
            sleep(10);
        }
    }
}

Markets::Run();
