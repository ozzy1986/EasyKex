<?php
/**
 * User: Antosha
 * Date: 25.04.14
 * Time: 17:23
 */

require_once( 'getAd.php' );
//$item = $result['items'][0];
// 4 testing
$item['type'] = 'video';
$item['media'][0]['url'] = "http://download.wavetlan.com/SVV/Media/HTTP/H264/Talkinghead_Media/H264_test4_Talkingheadclipped_mp4_480x320.mp4";
$item['secondTitle'] = "secondTitle";
//var_dump($item);die;
//$item['banner'] = "https://s3-us-west-1.amazonaws.com/ignitevideofuel-dev/demo/north+face/logo.png";
?>

<a class="wrapper" href="<?php echo $item['url']; ?>" target="_blank">
    <?php
    if( $item['type'] == 'video' ){
        if( $item['media'][0]['url'] ){
            echo '<video loop="" muted="" autoplay="" src="' . $item['media'][0]['url'] . '"></video>';
        }
    } else{
        echo "<div class='wholeImagesWrapper'>";
        $imageIterator = 0;
        foreach( $item['media'] as $image ){
            echo '<div class="imageWrapper"' . '" id="imageWrapper'.$imageIterator.'">'
                . '<img src="' . $image['url'] . '" alt="" class="moving ' . $image['direction'] . '" id="image' . $imageIterator . '"/>'
                . '<img src="' . $image['url'] . '" alt="" class="gag"/>' //gag for making the wrapper size as image has
                . '</div>';
            $imageIterator++;
        }
        echo "</div>";
    }
    ?>
    <?php if( $item['banner'] ){
        echo '<img class="logo" src="' . $item['banner'] . '" alt="' . $item['title'] . '"/>';
    }?>
    <div class="textLine1"><?php echo $item['title']; ?></div>
    <div class="textLine2"><?php echo $item['secondTitle']; ?></div>
    <div class="button"><?php echo $item['button']; ?></div>
</a>


