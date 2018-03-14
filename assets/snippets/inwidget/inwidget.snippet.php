<?php
if(!defined('MODX_BASE_PATH')){die('What are you doing? Get out of here!');}	
$login = isset($login) ? $login : 'fotokto_ru';
$hashtag = isset($hashtag) ? $hashtag : '';
$token = isset($token) ? $token : '';
$bannedLogins = isset($bannedLogins) ? $bannedLogins : '';
$imgRandom = isset($imgRandom) ? $imgRandom : false;
$imgCount = isset($imgCount) ? $imgCount : '3';
$cacheExpiration = isset($cacheExpiration) ? $cacheExpiration : '6';
$cacheSkip = isset($cacheSkip) ? $cacheSkip : false;
$dateFormat = isset($dateFormat) ? $dateFormat : '%d.%m.%Y'; 

$tpl = isset($tpl) ? $modx->getTpl($tpl) : '[+id+] [+code+] [+created+] [+date+] [+text+] [+link+] [+fullsize+] [+large+] [+small+] [+likesCount+] [+commentsCount+] [+authorId+]';
$outerTpl = isset($outerTpl) ? $modx->getTpl($outerTpl) : '[+userid+] [+username+] [+avatar+] [+posts+] [+followers+] [+following+] [+wrapper+]';	


	$config = array(
		'LOGIN' => $login,
		'HASHTAG' => $hashtag,
		'ACCESS_TOKEN' => $token,
		'tagsBannedLogins' => $bannedLogins,
		'tagsFromAccountOnly' => false,
		'imgRandom' => $imgRandom,
		'imgCount' => $imgCount,
		'cacheExpiration' => $cacheExpiration,
		'cacheSkip' => $cacheSkip,
		'cachePath' =>  MODX_BASE_PATH.'assets/cache/',
		'skinDefault' => 'default',
		'skinPath'=> 'skins/',
		'langDefault' => 'ru',
		'langAuto' => false,
		'langPath' => MODX_BASE_PATH.'assets/snippets/inwidget/langs/',
	);

//error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_WARNING);
//setlocale(LC_ALL, "ru_RU.UTF-8");
//header('Content-type: text/html; charset=utf-8');
if(phpversion() < "5.4.0") 		die('inWidget required PHP >= <b>5.4.0</b>. Your version: '.phpversion());
if(!extension_loaded('curl')) 	die('inWidget required <b>cURL PHP extension</b>. Please, install it or ask your hosting provider.');

#require_once 'classes/Autoload.php';
require_once MODX_BASE_PATH.'assets/snippets/inwidget/classes/InstagramScraper.php';
require_once MODX_BASE_PATH.'assets/snippets/inwidget/classes/Unirest.php';
require_once MODX_BASE_PATH.'assets/snippets/inwidget/classes/InWidget.php';


try {
	$inWidget = new \inWidget\Core($config);;
	$inWidget->getData();

	$i = 0;
	$count = $inWidget->countAvailableImages($inWidget->data->images);
	if($count>0) {
		$wrapper = '';
		if($inWidget->config['imgRandom'] === true) shuffle($inWidget->data->images);
		foreach ($inWidget->data->images as $key=>$item){
			if($inWidget->isBannedUserId($item->authorId) === true) continue;
			$item = json_decode(json_encode($item), true);
			$item['date'] = strftime($dateFormat,$item['created']);
			$wrapper .= $modx->parseText($tpl, $item, '[+', '+]' );
			$i++;
			if($i >= $imgCount) break;
		}
	}

	$data = json_decode(json_encode($inWidget->data), true);

	if (is_array($data)){
		$data['wrapper'] = $wrapper;
	}

	return $modx->parseText($outerTpl, $data, '[+', '+]' );

}
catch (\Exception $e) {
	echo $e->getMessage();
}
