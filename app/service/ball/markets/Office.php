<?php

namespace App\Service\Ball\Markets;

use App\Lib\Tools;

class Office extends AbstractBall
{
    public function marketNTF()
    {
        $originBalls = $this->getOriginBalls();
        $derivedBalls =$this->getDerivedBalls();
        $balls = array_merge($originBalls,$derivedBalls);
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
            "authority: api-mkt-ronin.apeironnft.com",
            "origin: https://marketplace.apeironnft.com",
            "referer: https://marketplace.apeironnft.com/",
            "user-agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36"
        ];
        $api = "https://api-mkt-ronin.apeironnft.com/v1/graphql";
        $row =<<<Row
{"operationName":"GetPlanets","variables":{"filterInput":{"coreType":[1,2,0,3],"nftListingStatus":["Listing"]},"pagingInput":{"offset":0,"first":500},"sortInput":{"sortBy":"PriceAsc"}},"query":"query GetPlanets(\$filterInput: GetPlanetsFilterInput!, \$pagingInput: PagingInput, \$sortInput: PlanetSortInput!) {\\n  getPlanets(\\n    filterInput: \$filterInput\\n    pagingInput: \$pagingInput\\n    sortInput: \$sortInput\\n  ) {\\n    edges {\\n      cursor\\n      node {\\n        ...PlanetLite\\n        __typename\\n      }\\n      __typename\\n    }\\n    pageInfo {\\n      ...PageInfo\\n      __typename\\n    }\\n    __typename\\n  }\\n}\\n\\nfragment PlanetLite on Planet {\\n  ...PlanetSuperLite\\n  air\\n  availableAttachTime\\n  bornTime\\n  breedCount\\n  breedCountMax\\n  childrenIDs\\n  coreType\\n  earth\\n  fire\\n  generation\\n  image\\n  lastBreedTime\\n  nftListingStatus\\n  planetCreateTime\\n  planetType\\n  priceInUSD\\n  stage\\n  status\\n  water\\n  parentIDs\\n  listingInfo {\\n    ...ListingInfo\\n    __typename\\n  }\\n  owner {\\n    ...Owner\\n    __typename\\n  }\\n  relicsTokens {\\n    description\\n    id\\n    image\\n    name\\n    thumbnail\\n    __typename\\n  }\\n  __typename\\n}\\n\\nfragment PlanetSuperLite on Planet {\\n  ageDisplay\\n  name\\n  planetID\\n  __typename\\n}\\n\\nfragment ListingInfo on ListingInfo {\\n  currencyCode\\n  duration\\n  endPrice\\n  startDate\\n  startPrice\\n  roninSaleOrder {\\n    kind\\n    orderKindEnum\\n    asset {\\n      erc\\n      addr\\n      id\\n      quantity\\n      __typename\\n    }\\n    expiredAt\\n    paymentToken\\n    startedAt\\n    basePrice\\n    endedAt\\n    endedPrice\\n    expectedState\\n    nonce\\n    marketFeePercentage\\n    signature\\n    orderStatus\\n    __typename\\n  }\\n  __typename\\n}\\n\\nfragment Owner on Account {\\n  avatar\\n  email\\n  name\\n  tag\\n  walletAddress\\n  createdAt\\n  __typename\\n}\\n\\nfragment PageInfo on PageInfo {\\n  ...PageInfoSkipTotal\\n  totalCount\\n  __typename\\n}\\n\\nfragment PageInfoSkipTotal on PageInfo {\\n  ...PageInfoCommon\\n  endCursor\\n  startCursor\\n  __typename\\n}\\n\\nfragment PageInfoCommon on CommonPageInfo {\\n  hasNextPage\\n  hasPreviousPage\\n  __typename\\n}"}
Row;
        $response = Tools::curl($api, $row, 1, $headers);
        $responseArray = json_decode($response, true);
        $balls = isset($responseArray["data"]["getPlanets"]["edges"]) ? $responseArray["data"]["getPlanets"]["edges"] : [];
        $items = [];
        foreach ($balls as $item) {
            $node = $item["node"];
            $id = $node["planetID"];
            $usdt = $node["priceInUSD"];
            $eth = $node["listingInfo"]["startPrice"];
            $breedCount = $node["breedCount"];
            $ronin = sprintf("%.3f", $usdt/$this->rate["ron"]["usd"]);
            array_push($items, $this->getItem($id, "origin", $eth, $usdt, $ronin, $breedCount));
        }

        return $items;
    }

    public function getDerivedBalls()
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
{"operationName":"GetStars","variables":{"filterInput":{"nftListingStatus":["Listing"],"starID":null},"pagingInput":{"offset":0,"first":500},"sortInput":{"sortBy":"PriceAsc"}},"query":"query GetStars(\$filterInput: GetStarsFilterInput!, \$pagingInput: PagingInput, \$sortInput: StarSortInput!) {\\n  getStars(\\n    filterInput: \$filterInput\\n    pagingInput: \$pagingInput\\n    sortInput: \$sortInput\\n  ) {\\n    edges {\\n      cursor\\n      node {\\n        ...StarLite\\n        __typename\\n      }\\n      __typename\\n    }\\n    pageInfo {\\n      ...PageInfo\\n      __typename\\n    }\\n    __typename\\n  }\\n}\\n\\nfragment StarLite on Star {\\n  ...StarSuperLite\\n  constellation\\n  constellationDigit\\n  constellationType\\n  galaxy\\n  galaxyImage\\n  starImage\\n  status\\n  listingInfo {\\n    ...ListingInfo\\n    __typename\\n  }\\n  __typename\\n}\\n\\nfragment StarSuperLite on Star {\\n  additionAgingBuffValue\\n  additionOrbitalTrackValue\\n  ageBuffValue\\n  nftListingStatus\\n  starID\\n  starName\\n  tier\\n  trackCountValue\\n  owner {\\n    ...Owner\\n    __typename\\n  }\\n  __typename\\n}\\n\\nfragment Owner on Account {\\n  avatar\\n  email\\n  name\\n  tag\\n  walletAddress\\n  createdAt\\n  __typename\\n}\\n\\nfragment ListingInfo on ListingInfo {\\n  currencyCode\\n  duration\\n  endPrice\\n  startDate\\n  startPrice\\n  roninSaleOrder {\\n    kind\\n    orderKindEnum\\n    asset {\\n      erc\\n      addr\\n      id\\n      quantity\\n      __typename\\n    }\\n    expiredAt\\n    paymentToken\\n    startedAt\\n    basePrice\\n    endedAt\\n    endedPrice\\n    expectedState\\n    nonce\\n    marketFeePercentage\\n    signature\\n    orderStatus\\n    __typename\\n  }\\n  __typename\\n}\\n\\nfragment PageInfo on PageInfo {\\n  ...PageInfoSkipTotal\\n  totalCount\\n  __typename\\n}\\n\\nfragment PageInfoSkipTotal on PageInfo {\\n  ...PageInfoCommon\\n  endCursor\\n  startCursor\\n  __typename\\n}\\n\\nfragment PageInfoCommon on CommonPageInfo {\\n  hasNextPage\\n  hasPreviousPage\\n  __typename\\n}"}
Row;

        $response = Tools::curl($api, $row, 1, $headers);
        $responseArray = json_decode($response, true);
        $balls = isset($responseArray["data"]["getStars"]["edges"]) ? $responseArray["data"]["getStars"]["edges"] : [];
        $items = [];
        foreach ($balls as $item) {
            $node = $item["node"];
            $id = $node["starID"];
            $eth = $node["listingInfo"]["startPrice"];
            $usdt = $eth * $this->rate["eth"]["usd"];
            $breedCount = -1;
            $name = $node["starName"];
            $ronin = $usdt/$this->rate["ron"]["usd"];
            array_push($items, $this->getItem($id, "derived", $eth, $usdt, $ronin, $breedCount,$name));
        }

        return $items;
    }

    public function setSrc()
    {
        $this->src = "marketplace.apeironnft.com";
    }
}
