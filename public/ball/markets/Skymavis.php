<?php
namespace Ball\Markets;
use Tools;

class Skymavis extends AbstractPipe{
    
    public function marketNTF()
    {
        $rate = $this->getRate();
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
            return false;
        }
        $balls = isset($responseArray["data"]["erc721Tokens"]["results"]) ? $responseArray["data"]["erc721Tokens"]["results"] : [];
        $items = [];

        foreach ($balls as $item) {
            $id = $item["tokenId"];
            $ronin = sprintf("%.3f",$item['order']["currentPrice"]/1000000000000000000);
            $usdt = sprintf("%.3f",$ronin * $rate["ron"]["usd"]);
            if($usdt < 100 ){
                $eth = $ronin;
                $usdt = sprintf("%.3f",$eth * $rate["eth"]["usd"]);
                $ronin = sprintf("%.3f",$usdt/$rate["ron"]["usd"]);
            }else{
                $eth = sprintf("%.3f",$usdt / $rate["eth"]["usd"]);
            }
           
            $breedCount = $item["attributes"]["conjunction count"][0];
            array_push($items, [
                "id"=>$id,
                "eth"=>$eth,
                "usdt"=>$usdt,
                "ronin"=>$ronin,
                "breedCount"=>$breedCount,
            ]);
        }
        $data = [
            "items"=>$items,
            "src"=>"https://marketplace.skymavis.com"
        ];

        return json_encode($data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }

    public function getRate(){
        // curl 'https://marketplace-graphql.skymavis.com/graphql' \
        // -H 'content-type: application/json' \
        // -H 'origin: https://marketplace.skymavis.com' \
        // -H 'referer: https://marketplace.skymavis.com/' \
        // -H 'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36' \
        // --data-raw '{"operationName":"NewEthExchangeRate","variables":{},"query":"query NewEthExchangeRate {\n  exchangeRate {\n    eth {\n      usd\n      __typename\n    }\n    slp {\n      usd\n      __typename\n    }\n    ron {\n      usd\n      __typename\n    }\n    axs {\n      usd\n      __typename\n    }\n    usd {\n      usd\n      __typename\n    }\n    __typename\n  }\n}\n"}' \
        // --compressed
        
        $api = "https://marketplace-graphql.skymavis.com/graphql";

        $headers = [
            "content-type: application/json",
            "origin: https://marketplace.skymavis.com",
            "referer: https://marketplace.skymavis.com/",
            "user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36"
        ];
        $row =<<<Row
{"operationName":"NewEthExchangeRate","variables":{},"query":"query NewEthExchangeRate {\\n  exchangeRate {\\n    eth {\\n      usd\\n      __typename\\n    }\\n    slp {\\n      usd\\n      __typename\\n    }\\n    ron {\\n      usd\\n      __typename\\n    }\\n    axs {\\n      usd\\n      __typename\\n    }\\n    usd {\\n      usd\\n      __typename\\n    }\\n    __typename\\n  }\\n}\\n"}
Row;
        $response = Tools::curl($api,$row,1,$headers);
        $responseArray = json_decode($response,true);

        return isset($responseArray["data"]["exchangeRate"]) ? $responseArray["data"]["exchangeRate"] : [];
    }
    
}
