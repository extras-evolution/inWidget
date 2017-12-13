<?php
if(!defined('MODX_BASE_PATH')){die('What are you doing? Get out of here!');}	
$login = isset($login) ? $login : 'fotokto_ru';
$hashtag = isset($hashtag) ? $hashtag : '';
$bannedLogins = isset($bannedLogins) ? $bannedLogins : '';
$imgRandom = isset($imgRandom) ? $imgRandom : false;
$imgCount = isset($imgCount) ? $imgCount : '3';
$cacheExpiration = isset($cacheExpiration) ? $cacheExpiration : '6';
$cacheSkip = isset($cacheSkip) ? $cacheSkip : false;
$dateFormat = isset($dateFormat) ? $dateFormat : '%d.%m.%Y'; 

$tpl = isset($tpl) ? $modx->getTpl($tpl) : '[+id+] [+code+] [+created+] [+date+] [+text+] [+link+] [+fullsize+] [+large+] [+small+] [+likesCount+] [+commentsCount+] [+authorId+]';
$outerTpl = isset($outerTpl) ? $modx->getTpl($outerTpl) : '[+userid+] [+username+] [+avatar+] [+posts+] [+followers+] [+following+] [+wrapper+]';	

$config = array(	
	'LOGIN' => $login, // Instagram login
	'HASHTAG' => $hashtag,// Separate hashtags by comma. For example: girl, man Use this options only if you want show pictures of other users. 
	'bannedLogins' => $bannedLogins,// Photos of these users will not be displayed in widget.Separate usernames by comma. For example: mark18, kitty45
	'imgRandom' => $imgRandom,// Random order of pictures [ true / false ]
    'imgCount' => $imgCount, // How many pictures widget will get from Instagram?
	'cacheExpiration' => $cacheExpiration, // Cache expiration time (hours)
	'cacheSkip' => $cacheSkip,//Warning! Use true option only for debug.
	'langDefault' => 'ru',// Default language [ ru / en ] or something else from lang directory.
	'langAuto' => false,// Language auto-detection [ true / false ] set language by $_GET variable.
	'cacheFile' => MODX_BASE_PATH.'assets/cache/{$LOGIN}.txt',
);

//error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_WARNING);
//setlocale(LC_ALL, "ru_RU.UTF-8");
//header('Content-type: text/html; charset=utf-8');
if(phpversion() < "5.4.0") 		die('inWidget required PHP >= <b>5.4.0</b>. Your version: '.phpversion());
if(!extension_loaded('curl')) 	die('inWidget required <b>cURL PHP extension</b>. Please, install it or ask your hosting provider.');

require_once MODX_BASE_PATH.'assets/snippets/inwidget/plugins/InstagramScraper/Traits/InitializerTrait.php';
require_once MODX_BASE_PATH.'assets/snippets/inwidget/plugins/InstagramScraper/Traits/ArrayLikeTrait.php';
require_once MODX_BASE_PATH.'assets/snippets/inwidget/plugins/InstagramScraper/Exception/InstagramNotFoundException.php';
require_once MODX_BASE_PATH.'assets/snippets/inwidget/plugins/InstagramScraper/Exception/InstagramAuthException.php';
require_once MODX_BASE_PATH.'assets/snippets/inwidget/plugins/InstagramScraper/Exception/InstagramException.php';
require_once MODX_BASE_PATH.'assets/snippets/inwidget/plugins/InstagramScraper/Model/AbstractModel.php';
require_once MODX_BASE_PATH.'assets/snippets/inwidget/plugins/InstagramScraper/Model/Tag.php';
require_once MODX_BASE_PATH.'assets/snippets/inwidget/plugins/InstagramScraper/Model/Media.php';
require_once MODX_BASE_PATH.'assets/snippets/inwidget/plugins/InstagramScraper/Model/Location.php';
require_once MODX_BASE_PATH.'assets/snippets/inwidget/plugins/InstagramScraper/Model/Comment.php';
require_once MODX_BASE_PATH.'assets/snippets/inwidget/plugins/InstagramScraper/Model/CarouselMedia.php';
require_once MODX_BASE_PATH.'assets/snippets/inwidget/plugins/InstagramScraper/Model/Account.php';
require_once MODX_BASE_PATH.'assets/snippets/inwidget/plugins/InstagramScraper/InstagramQueryId.php';
require_once MODX_BASE_PATH.'assets/snippets/inwidget/plugins/InstagramScraper/Endpoints.php';
require_once MODX_BASE_PATH.'assets/snippets/inwidget/plugins/InstagramScraper/Instagram.php';

require_once MODX_BASE_PATH.'assets/snippets/inwidget/plugins/unirest-php/Unirest/Request/Body.php';
require_once MODX_BASE_PATH.'assets/snippets/inwidget/plugins/unirest-php/Unirest/Request.php';
require_once MODX_BASE_PATH.'assets/snippets/inwidget/plugins/unirest-php/Unirest/Response.php';
require_once MODX_BASE_PATH.'assets/snippets/inwidget/plugins/unirest-php/Unirest/Method.php';
require_once MODX_BASE_PATH.'assets/snippets/inwidget/plugins/unirest-php/Unirest/Exception.php';

require_once MODX_BASE_PATH.'assets/snippets/inwidget/inwidget.php';

$inWidget = new inWidget($config);
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