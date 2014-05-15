<?php

require '../commLib.php';

$id = (int)$_GET["id"];


$GLOBALS["defaultTemplate"] = array(
    array(
        "width" => 300,
        "height" => 250,
        "templateType" => "standard",
        "tracking" => "http://dev.adtrack.api.fuel451.com/",
        "loop" => 0,
        "format" => "json",
        "crossdomain" => "https://s3.amazonaws.com/"
    ));

$result = getAD($id);

//jsonAnswer($result);


/**
 * Core for product feed campaign
 * @param $adId
 */
function getAD($id)
{
    $ad = getAdProduct($id);
    if ($ad) {
        $items = array();
        $baseItem = getItem($ad);
        if (!$baseItem) return $GLOBALS["defaultFlashAd"];
        $items[] = $baseItem;

        $ad2 = getrandomAdProduct($ad['bid'], array($ad['id']));
        if ($ad2) {
            $item2 = getItem($ad2);
            if ($item2) $items[] = $item2;
            $ad3 = getrandomAdProduct($ad2['bid'], array($ad['id'], $ad2['id']));
            if ($ad3) {
                $item3 = getItem($ad2);
                if ($item3) $items[] = $item3;
            }
        }
        $result['status'] = "success";
        $result['id'] = 0;
        $result['uid'] = $ad['uid'];
        $result['adType'] = 'pf';
        $result['template'] = $GLOBALS["defaultTemplate"];
        $result['items'] = $items;

        return $result;

    } else {
        return $GLOBALS["defaultFlashAd"];
    }

}

/**
 * @param $pid
 * @param $bid
 * @return ad
 */
function getAdProduct($id)
{
    $sql = sprintf("SELECT c.* FROM adProduct c WHERE c.id=%d  LIMIT 1", $id);
    $res = mysql_query($sql) or debug(mysql_error());
    return ($res && ($row = mysql_fetch_assoc($res))) ? $row : false;
}

function getrandomAdProduct($bid, $notIn = false)
{
    $notFilter = (is_array($notIn)) ? " AND NOT c.pid IN (" . implode(', ', $notIn) . ")" : '';
    $sql = sprintf("SELECT c.* FROM adProduct c WHERE c.bid=%d $notFilter ORDER BY RAND() LIMIT 1", $bid);
    $res = mysql_query($sql) or debug(mysql_error());
    return ($res && ($row = mysql_fetch_assoc($res))) ? $row : false;
}

/**
 * @param $videoIds
 * @return array|bool
 */
function getVideoByID($vid)
{
    $sql = sprintf("SELECT v.* FROM adEncodedVideo v WHERE v.id=%d", $vid);
    $res = mysql_query($sql) or debug(mysql_error());
    return ($res && ($row = mysql_fetch_assoc($res))) ? $row : false;
}

/**
 * @param $imageIds
 * @return array|bool
 */
function getImageByIds($imageIds)
{
    if (!$imageIds) return false;
    $imageIds = (is_array($imageIds)) ? $imageIds : array($imageIds);
    $ids = implode(', ', $imageIds);
    $sql = "SELECT i.* FROM image i WHERE i.id in ($ids) ORDER BY RAND() LIMIT 3";
    $res = mysql_query($sql) or debug(mysql_error());
    $result = array();
    while ($row = mysql_fetch_assoc($res)) {
        $result[] = $row;
    }
    return (count($result) > 0) ? $result : false;
}

/**
 * @param $productId
 * @return array|bool
 */
function getProduct($productId)
{
    $sql = sprintf("SELECT p.* FROM product p WHERE p.id=%d ", $productId);
    $res = mysql_query($sql) or debug(mysql_error());
    return ($res && ($row = mysql_fetch_assoc($res))) ? $row : false;
}

/**
 * @param $id
 * @return array|bool
 */
function getAdAsset($id)
{
    $sql = sprintf("SELECT * FROM accountAdAsset WHERE id=%d", $id);
    $res = mysql_query($sql) or debug(mysql_error());
    return ($res && ($row = mysql_fetch_assoc($res))) ? $row : false;
}

/**
 * @param $id
 * @return bool
 */
function getAssetTypeById($id)
{
    $sql = sprintf("SELECT * FROM accountAdAssetType WHERE id=%d LIMIT 1", $id);
    $res = mysql_query($sql) or debug(mysql_error());
    return ($res && ($row = mysql_fetch_assoc($res))) ? $row['name'] : false;
}

function getItem($ad)
{
    $video = false;
    $imagesList = array();

    if ($ad['vid']) {
        // selecting videos
        $vids = explode('/', $ad["vid"]);
        $vid_key = array_rand($vids, 1);
        $vid = $vids[$vid_key];
        $video = getVideoByID($vid);

    } elseif ($ad['kbiids']) {
        //selecting images
        $imageIds = explode('/', $ad['kbiids']);
        $imagesList = getImageByIds($imageIds);
    } else {
        return false;
    }

    if ($video || (count($imagesList) > 0)) {
        $product = getProduct($ad['pid']);
        if ($product) {
            $baseItem = array(
                "id" => $product['id'],
                "title" => $product['name'],
                "loop" => 0,
                "secondTitle" => $product['secondtTitle'],
                "url" => $product['productURL'],
                "color" => array(
                    "background" => "0xFFFFFF",
                    "button" => "0xCC0000",
                    "price" => "0xCC0000"
                ),
                "button" => ""
            );
            if ($product['msrp']) $baseItem['msrp'] = $product['msrp'];
            if ($product['sale']) $baseItem['sale'] = $product['sale'];
            if ($ad['button']) $baseItem['button'] = $ad['button'];
            //$baseItem['banner'] = 'https://s3-us-west-1.amazonaws.com/ignitevideofuel-dev/demo/TeedHaze/teedhazebannerb.jpg';
            $baseItem['banner'] = ($img = getImageByIds($product['lid'])) ? "https://" . $GLOBALS["s3URL"] . "/" . $GLOBALS["s3Bucket"] . "/images/adlogo/" . $product['uid'] . "/" . $imagesList[0]['filename'] : "";
            if ($video) {
                $baseItem["type"] = "video";
                $baseItem["media"] = array(
                    array(
                        'id' => $video['id'],
                        'url' => $video['sourceURL'],
                        'loop' => 1
                    )
                );
            } elseif ($imagesList) {
                $images = array();
                if ($imagesList[0]) {
                    $baseItem['defaultImage'] = "https://" . $GLOBALS["s3URL"] . "/" . $GLOBALS["s3Bucket"] . "/" . $GLOBALS["productImageRoute"] . "/" . $product['uid'] . "/" . $imagesList[0]['filename'];
                    $images[] = array(
                        'id' => 1,
                        'url' => "https://" . $GLOBALS["s3URL"] . "/" . $GLOBALS["s3Bucket"] . "/" . $GLOBALS["productImageRoute"] . "/" . $product['uid'] . "/" . $imagesList[0]['filename'],
                        'direction' => 'left',
                        'scale' => 'none'
                    );
                }
                if ($imagesList[1]) {
                    $images[] = array(
                        'id' => 2,
                        'url' => "https://" . $GLOBALS["s3URL"] . "/" . $GLOBALS["s3Bucket"] . "/" . $GLOBALS["productImageRoute"] . "/" . $product['uid'] . "/" . $imagesList[1]['filename'],
                        'direction' => 'right',
                        'scale' => 'none'
                    );
                }
                if ($imagesList[2]) {
                    $images[] = array(
                        'id' => 3,
                        'url' => "https://" . $GLOBALS["s3URL"] . "/" . $GLOBALS["s3Bucket"] . "/" . $GLOBALS["productImageRoute"] . "/" . $product['uid'] . "/" . $imagesList[2]['filename'],
                        'direction' => 'up',
                        'scale' => 'none'
                    );
                }
                $baseItem["type"] = "image";
                $baseItem["media"] = $images;
            }
            return $baseItem;
        }
    }
    return false;
}