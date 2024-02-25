<?php

namespace App\Service\Ball\Markets;

use App\Lib\Tools;

class Skymavis extends AbstractBall
{
    public function marketNTF()
    {
        $originBalls = $this->getOriginBalls();
        $derivedBalls =$this->getDerivedBalls();
        $balls = array_merge($originBalls, $derivedBalls);
        $data = [
            "items"=>$balls,
            "src"=>$this->src
        ];
        return $data;
    }

    public function getOriginBalls()
    {
        $headers = [
            "content-type: application/json",
            "origin: https://marketplace.skymavis.com",
            "referer: https://marketplace.skymavis.com/",
            "user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36"
        ];
        $api = "https://marketplace-graphql.skymavis.com/graphql";
        $row =<<<Row
{"operationName":"GetERC721TokensList","variables":{"from":0,"auctionType":"Sale","size":48,"sort":"PriceAsc","criteria":[{"name":"exordium","values":["primal exordium","divine exordium","arcane exordium","mythic exordium"]}],"tokenAddress":"0x3672f99418ac1dfd71147dbd7c05d4a7aab7aae4","rangeCriteria":[]},"query":"query GetERC721TokensList(\$tokenAddress: String\u0021, \$owner: String, \$auctionType: AuctionType, \$criteria: [SearchCriteria\u0021], \$from: Int\u0021, \$size: Int\u0021, \$sort: SortBy, \$name: String, \$priceRange: InputRange, \$rangeCriteria: [RangeSearchCriteria\u0021]) {\\n  erc721Tokens(\\n    tokenAddress: \$tokenAddress\\n    owner: \$owner\\n    auctionType: \$auctionType\\n    criteria: \$criteria\\n    from: \$from\\n    size: \$size\\n    sort: \$sort\\n    name: \$name\\n    priceRange: \$priceRange\\n    rangeCriteria: \$rangeCriteria\\n  ) {\\n    total\\n    results {\\n      ...Erc721TokenBrief\\n      __typename\\n    }\\n    __typename\\n  }\\n}\\n\\nfragment Erc721TokenBrief on Erc721 {\\n  tokenAddress\\n  tokenId\\n  owner\\n  name\\n  order {\\n    ...OrderInfo\\n    __typename\\n  }\\n  image\\n  cdnImage\\n  video\\n  isLocked\\n  attributes\\n  traitDistribution {\\n    ...TokenTrait\\n    __typename\\n  }\\n  collectionMetadata\\n  ownerProfile {\\n    name\\n    accountId\\n    __typename\\n  }\\n  __typename\\n}\\n\\nfragment OrderInfo on Order {\\n  id\\n  maker\\n  kind\\n  assets {\\n    ...AssetInfo\\n    __typename\\n  }\\n  expiredAt\\n  paymentToken\\n  startedAt\\n  basePrice\\n  expectedState\\n  nonce\\n  marketFeePercentage\\n  signature\\n  hash\\n  duration\\n  timeLeft\\n  currentPrice\\n  suggestedPrice\\n  makerProfile {\\n    ...PublicProfileBrief\\n    __typename\\n  }\\n  orderStatus\\n  orderQuantity {\\n    orderId\\n    quantity\\n    remainingQuantity\\n    availableQuantity\\n    __typename\\n  }\\n  __typename\\n}\\n\\nfragment AssetInfo on Asset {\\n  erc\\n  address\\n  id\\n  quantity\\n  __typename\\n}\\n\\nfragment PublicProfileBrief on PublicProfile {\\n  accountId\\n  addresses {\\n    ...Addresses\\n    __typename\\n  }\\n  activated\\n  name\\n  __typename\\n}\\n\\nfragment Addresses on NetAddresses {\\n  ethereum\\n  ronin\\n  __typename\\n}\\n\\nfragment TokenTrait on TokenTrait {\\n  key\\n  value\\n  count\\n  percentage\\n  __typename\\n}\\n"}
Row;
        $response = Tools::curl($api, $row, 1, $headers);
        $responseArray = json_decode($response, true);
        if (isset($responseArray["errors"])) {
            return [];
        }
        $balls = isset($responseArray["data"]["erc721Tokens"]["results"]) ? $responseArray["data"]["erc721Tokens"]["results"] : [];
        $items = [];

        foreach ($balls as $item) {
            $id = $item["tokenId"];
            $contracts = $item["order"]["paymentToken"];
            $proice = sprintf("%.3f", $item['order']["currentPrice"]/1000000000000000000);
            switch ($contracts) {
                    // eth支付
                case "0xc99a6a985ed2cac1ef41640596c5a5f9f4e19ef5":
                    $eth = $proice;
                    $usdt = sprintf("%.3f", $eth * $this->rate["eth"]["usd"]);
                    $ronin = sprintf("%.3f", $usdt/$this->rate["ron"]["usd"]);
                    break;
                default:
                    // ron支付
                    // 0xe514d9deb7966c8be0ca922de8a064264ea6bcd4
                    $usdt = sprintf("%.3f", $proice * $this->rate["ron"]["usd"]);
                    $ronin = sprintf("%.3f", $usdt/$this->rate["ron"]["usd"]);
                    $eth = sprintf("%.3f", $usdt/$this->rate["eth"]["usd"]);
                    break;
            }

            $breedCount = $item["attributes"]["conjunction count"][0];

            preg_match("/[\w]+/", $item["name"], $name);
            $name = isset($name[0]) ? $name[0] : '';

            array_push($items, $this->getItem($id, "origin", $eth, $usdt, $ronin, $breedCount,$name));
        }
        return $items;
    }

    public function getDerivedBalls()
    {
        $headers = [
            "content-type: application/json",
            "origin: https://marketplace.skymavis.com",
            "referer: https://marketplace.skymavis.com/",
            "user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36"
        ];
        $api = "https://marketplace-graphql.skymavis.com/graphql";
        $row =<<<Row
{"operationName":"GetERC721TokensList","variables":{"from":0,"auctionType":"Sale","size":50,"sort":"PriceAsc","tokenAddress":"0xa1d7b6d5d9d6749a17e5c9411b1be90e0f2738e1","rangeCriteria":[]},"query":"query GetERC721TokensList(\$tokenAddress: String!, \$owner: String, \$auctionType: AuctionType, \$criteria: [SearchCriteria!], \$from: Int!, \$size: Int!, \$sort: SortBy, \$name: String, \$priceRange: InputRange, \$rangeCriteria: [RangeSearchCriteria!]) {\\n  erc721Tokens(\\n    tokenAddress: \$tokenAddress\\n    owner: \$owner\\n    auctionType: \$auctionType\\n    criteria: \$criteria\\n    from: \$from\\n    size: \$size\\n    sort: \$sort\\n    name: \$name\\n    priceRange: \$priceRange\\n    rangeCriteria: \$rangeCriteria\\n  ) {\\n    total\\n    results {\\n      ...Erc721TokenBrief\\n      __typename\\n    }\\n    __typename\\n  }\\n}\\n\\nfragment Erc721TokenBrief on Erc721 {\\n  tokenAddress\\n  tokenId\\n  owner\\n  name\\n  order {\\n    ...OrderInfo\\n    __typename\\n  }\\n  image\\n  cdnImage\\n  video\\n  isLocked\\n  attributes\\n  traitDistribution {\\n    ...TokenTrait\\n    __typename\\n  }\\n  collectionMetadata\\n  ownerProfile {\\n    name\\n    accountId\\n    __typename\\n  }\\n  __typename\\n}\\n\\nfragment OrderInfo on Order {\\n  id\\n  maker\\n  kind\\n  assets {\\n    ...AssetInfo\\n    __typename\\n  }\\n  expiredAt\\n  paymentToken\\n  startedAt\\n  basePrice\\n  expectedState\\n  nonce\\n  marketFeePercentage\\n  signature\\n  hash\\n  duration\\n  timeLeft\\n  currentPrice\\n  suggestedPrice\\n  makerProfile {\\n    ...PublicProfileBrief\\n    __typename\\n  }\\n  orderStatus\\n  orderQuantity {\\n    orderId\\n    quantity\\n    remainingQuantity\\n    availableQuantity\\n    __typename\\n  }\\n  __typename\\n}\\n\\nfragment AssetInfo on Asset {\\n  erc\\n  address\\n  id\\n  quantity\\n  __typename\\n}\\n\\nfragment PublicProfileBrief on PublicProfile {\\n  accountId\\n  addresses {\\n    ...Addresses\\n    __typename\\n  }\\n  activated\\n  name\\n  __typename\\n}\\n\\nfragment Addresses on NetAddresses {\\n  ethereum\\n  ronin\\n  __typename\\n}\\n\\nfragment TokenTrait on TokenTrait {\\n  key\\n  value\\n  count\\n  percentage\\n  __typename\\n}\\n"}
Row;
        $response = Tools::curl($api, $row, 1, $headers);
        $responseArray = json_decode($response, true);
        if (isset($responseArray["errors"])) {
            return [];
        }
        $balls = isset($responseArray["data"]["erc721Tokens"]["results"]) ? $responseArray["data"]["erc721Tokens"]["results"] : [];
        $items = [];

        foreach ($balls as $item) {
            $id = $item["tokenId"];
            $contracts = $item["order"]["paymentToken"];
            $proice = sprintf("%.3f", $item['order']["currentPrice"]/1000000000000000000);
            switch ($contracts) {
                    // eth支付
                case "0xc99a6a985ed2cac1ef41640596c5a5f9f4e19ef5":
                    $eth = $proice;
                    $usdt = sprintf("%.3f", $eth * $this->rate["eth"]["usd"]);
                    $ronin = sprintf("%.3f", $usdt/$this->rate["ron"]["usd"]);
                    break;
                default:
                    // ron支付
                    // 0xe514d9deb7966c8be0ca922de8a064264ea6bcd4
                    $usdt = sprintf("%.3f", $proice * $this->rate["ron"]["usd"]);
                    $ronin = sprintf("%.3f", $usdt/$this->rate["ron"]["usd"]);
                    $eth = sprintf("%.3f", $usdt/$this->rate["eth"]["usd"]);
                    break;
            }

            $breedCount = -1;
            preg_match("/[\w]+/", $item["name"], $name);
            $name = isset($name[0]) ? $name[0] : '';
            array_push($items, $this->getItem($id, "derived", $eth, $usdt, $ronin, $breedCount, $name));
        }
        return $items;
    }

    public function setSrc()
    {
        $this->src = "marketplace.skymavis.com";
    }
}
