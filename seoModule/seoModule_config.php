<?php
/**
 * include_once('_buran/seoModule.php'); - в начало скрипта-обработчика путей (смотреть .htaccess)
 *
 * Если в Битриксе слетает авторизация,
 * Настройки -> Пользователи -> Группы -> Безопасность -> установите маски: 0.0.0.0
 *
 * Если сайт на www.1gb.ru - отправлять заголовок x-1gb-client-ip
 */

// Redirects --------------------------------------------------------
$redirects= array(
	'global' => array(
		// '+/(.*)\.html$/' => '${1}/',
		// '+/[^\/]$/'      => '${0}/',
		'/index.php'  => '/',
		'/index.html' => '/',
	),
);
// Redirects --------------------------------------------------------

/*
$websites= array(
	1 => array(
		'https://www.subdomain.domain.com'    - Протокол и домен
		'/page_uri'                           - URI главной страницы
		'/page_uri'                           - URI страницы донора
		'/page_uri'                           - URI страницы списка статей
		0000                                  - ID в бункере
	),
);
*/
$websites= array(
	1 => array('https://www.subdomain.domain.com', '/', '/', '/', 0000),
);

$seopages= array(
	'global' => array(
		'/produktsiya/kachestvo-otzyvy-ispytaniya.php'           => 'A:agrosnab',
		'/produktsiya/borony.php'                                => 'S:borona',
		'/produktsiya/borony.php?ELEMENT_ID=44&SECTION_ID='      => 'W:borona_diskovaya',
	),
);

$configs= array(
	'global' => array(
		'module_enabled'     => true, // активность модуля ... можно указать IP-адрес '80.80.109.182'
		's_page_suffix'      => '.html', // суффикс S статей
		'get_content_method' => 'curl', // curl // stream
		'tx_path'            => '/tx', // путь к папке со статьями      '/tx'
		'img_path'           => '/tx/img', // путь к папке с картинками '/tx/img'
		'use_share'          => true, // блок поделиться
		'img_crop'           => true, // кропить картинки
		'img_width'          => 300,
		'img_height'         => 200,
		'in_charset'         => 'utf-8', // кодировка текстов
		'out_charset'        => 'utf-8', // целевая кодировка
		'base'               => 'replace_or_add', // replace_or_add // replace_if_exists // delete // false
		'canonical'          => 'replace_or_add', // replace_or_add // replace_if_exists // delete // false
		'meta'               => 'replace_or_add', // replace_or_add // replace_if_exists // delete // false
		'requets_methods'    => '/GET/HEAD/',
		'https_test'         => false, // true - для тестирования оптимизации на домене с HTTPS, но без сертификата
		'hide_opt'           => false, // true // false // SAW // S // A // W // WA // SW - скрывать текст за стрелочкой
		'curl_auto_redirect' => false, // авторедирект для CURL
		'cookie'             => true, // сохранять печеньки
		'set_header'         => true, // возвращать заголовки
	),
);

$configs['global']['share_code']= '<script src="//yastatic.net/es5-shims/0.0.2/es5-shims.min.js"></script><script src="//yastatic.net/share2/share.js"></script><div class="ya-share2" data-services="vkontakte,facebook,odnoklassniki,moimir,twitter,viber,whatsapp,skype,telegram" data-counter=""></div>';

$configs['global']['styles']= '<style>
	.sssmodulebox {padding:0 0 20px; font-size:1em; line-height:1em;}
	.sssmodulebox .sssmb_h1 h1 {margin:1.2em 0 1em; line-height:1.2em;}
	.sssmodulebox .sssmb_stext {padding:0 0 20px}
	.sssmodulebox .sssmb_stext h2,
	.sssmodulebox .sssmb_stext h3,
	.sssmodulebox .sssmb_stext h4 {margin:1.2em 0 1em; line-height:1.3em;}
	.sssmodulebox .sssmb_stext p {line-height:1.5em; padding:5px 0;}
	.sssmodulebox .sssmb_stext ul {list-style:disc; margin:1em 0 1em 2em;}
	.sssmodulebox .sssmb_stext ul li {display:list-item; margin:0; padding:5px 0 5px 1em; line-height:1.2em;}
	.sssmodulebox .yasharebox ul li {background:none; padding-left:0;}
</style>';

/*
 * Место в коде для вставки текста
 * Берется первый подходящий участок кода поэтому учитывайте порядок
 * Код копировать "как есть" - со всеми переносами и т.д.
 * Спец.символ в начале строки указывает на тип вставки:
 * 		% - добавить ПЕРЕД
 * 		@ - замена
 * 		# - добавить ПОСЛЕ
 */

$content_start['global'][]= '#<!-- sssmodule_start -->';

// --------------------

$content_finish['global'][]= '%<!-- sssmodule_finish -->';

// --------------------
