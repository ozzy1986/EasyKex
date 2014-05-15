<?php
$GLOBALS["s3Bucket"] = 'ignitevideo_development';
$GLOBALS["s3URL"]='s3.amazonaws.com';
$GLOBALS["productImageRoute"]='images/adProducts';
$GLOBALS["accountImageAssetsRoute"]='images/accountAssets';
$GLOBALS["encodedVideoRoute"]='adEncoded';
$GLOBALS["adLogoRoute"]='images/adLogo';
$GLOBALS["dbhost"] = "54.241.31.244";
$GLOBALS["dbname"] = "fuelAsset";
$GLOBALS["dbUser"] = "igvdb_rw";
$GLOBALS["dbPassword"] = "igvdbadmin1013";
$GLOBALS["debug"] = true;

$GLOBALS["defaultFlashAd"]='
{
	"status": "success",
	"id": "1",
	"adType":"pf",
	"uid": "21",
	"template": [
		{
			"width": 300,
			"height": 250,
			"theme": "standard",
			"loop": 1,
			
			"tracking":"http://dev.adtrack.api.fuel451.com/",
			"format": "json",
                        "crossdomain": "http://dev.s.fuel451.com/"
		}
	],
	"items": [
		{
			"id": "3",
			"title": "",
			"msrp": "",
			"price": "",
			"url": "http://teedhaze.com/",
			"button": "",
			"color": {
				"background": "0xFFFFFF",
				"button": "0xCC0000",
				"price": "0xCC0000"
			},
			"banner": "https://s3-us-west-1.amazonaws.com/ignitevideofuel-dev/demo/TeedHaze/teedhazebannerb.jpg",
			"banner2": "",
"type": "video",
			"media": [
				{
					"id": "1",
					"url": "https://s3-us-west-1.amazonaws.com/ignitevideofuel-dev/demo/TeedHaze/teedhaze.flv",				
					"loop": 1
			 	}
			]
		},
		{
			"id": "2",
			"title": "",
			"msrp": "",
			"price": "",
			"url": "http://teedhaze.com/",
			"button": "",
			"color": {
				"background": "0xFFFFFF",
				"button": "0xCC0000",
				"price": "0xCC0000"
			},
			"banner": "https://s3-us-west-1.amazonaws.com/ignitevideofuel-dev/demo/TeedHaze/teedhazebannerR.jpg",
			"banner2": "",
			"type": "video",
			"media": [
				{
					"id": "2",
					"url": "https://s3-us-west-1.amazonaws.com/ignitevideofuel-dev/demo/TeedHaze/th+prop+2.flv",
		
					"loop": 1
			 	}
			]
		}
	]
}

';
?>
