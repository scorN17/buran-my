<?php
/*
- много сайтовость
	+ редиректы
	+ редиректы на основной домен
- кэш шаблонов
	+ логи
	+ логировать ошибки
	+ выдача по запросу хэш суммы оптимизации
	+ история хэш сумм
	+ поиск участка кода для вставки текста ... по тегу // заменить или добавить до или после или класс элемента
	+ тэг куда вставить ссылку на страницу статьи
- скрытая оптимизация
- города - мега модуль
- robots.txt
	+ WordPress - при залогине в админке - не рабит сайт
- Expires в Netcat
- на S страницах заменять H1
*/



/**
 * Если в ёбаном Битриксе слетает авторизация, удалите его нахуй
 * или Настройки -> Пользователи -> Группы -> Безопасность -> установите маски: 0.0.0.0
 */

// Redirects --------------------------------------------------------
$redirects= array(
	1 => array(
		'/index.php'         => '/',
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
$websites[1]= array('http://.ru', '/', '/stati/', '/stati/'); 

$seopages= array(
	1 => array(
		'/dfgdfgdfg/'                                               => 'A:dfgdfgdfg',
	),
);

$config= array(
	'module_enabled'     => true, // Активность модуля ... можно указать IP-адрес 80.80.109.182
	's_page_suffix'      => '/', // суффикс S статей с точкой в начале
	'get_content_method' => 'curl', // curl // file_get_contents
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
	'https_test'         => true, // true - для тестирования оптимизации на домене с HTTPS, но без сертификата
);

$config['share_code']= '<script src="//yastatic.net/es5-shims/0.0.2/es5-shims.min.js"></script><script src="//yastatic.net/share2/share.js"></script><div class="ya-share2" data-services="vkontakte,facebook,odnoklassniki,moimir,twitter,viber,whatsapp,skype,telegram" data-counter=""></div>';

$seo_text_styles= '<style>
	.sssmodulebox {padding:0 0 20px; font-size:1em; line-height:1em;}
	.sssmodulebox .sssmb_h1 h1 {margin:1.3em 0 1em;}
	.sssmodulebox .sssmb_stext {padding:0 0 20px}
	.sssmodulebox .sssmb_stext h2,
	.sssmodulebox .sssmb_stext h3,
	.sssmodulebox .sssmb_stext h4 {margin:1.2em 0 1em;}
	.sssmodulebox .sssmb_stext p {line-height:1.5em; padding:5px 0;}
	.sssmodulebox .sssmb_stext ul {list-style:disc; margin:1em 0 1em 2em;}
	.sssmodulebox .sssmb_stext ul li {display:list-item; margin:0; padding:5px 0 5px 1em;}
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

$content_start[]= '#<!-- Article -->
	<article>
		<div class="row">';
$content_start[]= '#<section class="article-content clearfix">';

// --------------------

$content_finish[]= '%</div>
	</article>
	<!-- //Article -->';
	
$content_finish[]= '%</section>';




// --------------------

// $articles_link[]= '#';

// --------------------
