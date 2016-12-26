<?php
/*
- тестировать редиректы на POST запросах
- проход по всем страницам сайта собственными силами сайта
- sitemap
- robots.txt
- ссылка на статьи только на опт.страницах - исправить
*/



/**
 * include_once('_buran/seoModule.php'); - в начало скрипта-обработчика путей (смотреть .htaccess)
 *
 * Если в Битриксе слетает авторизация,
 * Настройки -> Пользователи -> Группы -> Безопасность -> установите маски: 0.0.0.0
 *
 * Собственной функции определения кодировки пока нет. Когда понадобится - добавим.
 *
 * Получение контента донора пока только через CURL. Другие методы добавим по мере надобности.
 */

// Redirects --------------------------------------------------------
$redirects= array(
	'global' => array(
		'/index.html' => '/',
		'/index.php'  => '/',
	),
);
// Redirects --------------------------------------------------------

/*
$websites[1]= array(
	'https://www.subdomain.domain.com'
	'/page_uri' - URI главной страницы
	'/page_uri' - URI страницы донора
	'/page_uri' - URI страницы списка статей
)
*/
$websites= array(
	1 => array('http://domain.ru', '/', '/', '/'),
);

$seopages= array(
	'global' => array(
		'/dfgdfgdfg/' => 'A:dfgdfgdfg',
	),
);

$configs= array(
	'global' => array(
		'debug'              => false, // влючать только на время тестирования
		'module_enabled'     => true, // активность модуля ... можно указать IP-адрес '80.80.109.182'
		's_page_suffix'      => '.html', // суффикс S статей
		'get_content_method' => 'curl', // curl // file_get_contents // socket
		'tx_path'            => '/tx', // путь к папке со статьями      '/tx'
		'img_path'           => '/tx/img', // путь к папке с картинками '/tx/img'
		'use_share'          => true, // блок поделиться
		'img_width'          => 300,
		'img_height'         => 200,
		'checkcharsetmethod' => 'mb_detect_encoding', // mb_detect_encoding // own_function - метод определения кодировки
		'toencoding'         => 'utf-8', // целевая кодировка текстов
		'base'               => 'replace_or_add', // replace_or_add // replace_if_exists // delete // false
		'canonical'          => 'replace_or_add', // replace_or_add // replace_if_exists // delete // false
		'meta'               => 'replace_or_add', // replace_or_add // replace_if_exists // delete // false
		'requets_methods'    => '/GET/HEAD/',
		'https_test'         => false, // true - для тестирования оптимизации на домене с HTTPS, но без сертификата
		'display_errors'     => 'on', // 'on' | 'off' - ini_set('display_errors');
		'error_reporting'    => E_ALL & ~E_NOTICE, // error_reporting();
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

$content_start['global'][]= '#<!--content_start-->';

// --------------------

$content_finish['global'][]= '%<!--content_end-->';

// --------------------

// $articles_link['global'][]= '#';

// --------------------
