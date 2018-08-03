<?php
/**
 * include_once('_buran/seoModule.php'); - в начало скрипта-обработчика путей (смотреть .htaccess)
 *
 * Если в Битриксе слетает авторизация,
 * Настройки -> Пользователи -> Группы -> Безопасность -> установите маски: 0.0.0.0
 *
 * Если сайт на www.1gb.ru - отправлять заголовок x-1gb-client-ip
 */

/*
[01] | Основная ошибка запуска оптимизации
[10] | Ошибка CURL
[11] | Ошибка stream
[20] | Не 200 ответ
[21] | Не могу подключить опт.файл
[30] | Финиш не найден или s_text пустой
[31] | Старт не задан
[32] | Старт не найден
[40] | Опт.файл не найден
[41] | Пустая страница
[50] | Ошибка title, description, keywords
[51] | Ошибка base
[52] | Ошибка base
[53] | Ошибка canonical
[54] | Ошибка canonical
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
		'city'                                - Город
		'2011-11-13'                          - Дата публикации
		''                                    - Наименование организации
		'/path/name.jpg'                      - Относительный путь к логотипу
		'+7 000 000-00-00'                    - Телефон с +7
		'000000, Россия, Ростовская об ...'   - Полный адрес
	),
);
*/
$websites= array(
	1 => array('https://www.subdomain.domain.com',
		'/',
		'/',
		'/',
		0000,
		'Ростов-на-Дону',
		'2017-11-13',
		'ООО «Наименование»',
		'/images/logo.png',
		'+7 918 643-50-25',
		'352630, Россия, Краснодарский край, г. Белореченск, пер. Химиков, 19',
	),
);

$seopages= array(
	'global' => array(
		'/articles.html'
		=> 'S:articles',

		'/xxxxxxxxxxxxxx' 			=> 'A:xxxxxxxxxxxxxx',
		'/xxxxxxxxxxxxxx' 			=> 'S:xxxxxxxxxxxxxx',
		'/xxxxxxxxxxxxxx' 			=> 'W:xxxxxxxxxxxxxx',
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
		'urldecode'          => true, // декодировать адреса
		'city_replace'       => false, // подставлять город
		'redirect'           => true, // переадресовывать
		'classname'          => '', // имя класса для контейнера опимизации
	),
);


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



$declension['Ростов-на-Дону']= array(1 => 'Ростов-на-Дону', 6 => 'Ростове-на-Дону');



$configs['global']['share_code']= '<script src="//yastatic.net/es5-shims/0.0.2/es5-shims.min.js"></script><script src="//yastatic.net/share2/share.js"></script><div class="ya-share2" data-services="vkontakte,facebook,odnoklassniki,moimir,twitter,viber,whatsapp,skype,telegram" data-counter=""></div>';

$configs['global']['styles']= '<style>
	.sssmodulebox {padding:0 0 20px; font-size:1em; line-height:1em;}
	.sssmodulebox h1,
	.sssmodulebox h2,
	.sssmodulebox h3 {
		margin:1em 0 .7em !important;
		line-height:1.2em !important;
	}
	.sssmodulebox .sssmb_cinf {
		display:none;
	}
	.sssmodulebox .sssmb_stext {
		padding:0 0 20px;
		text-align:left;
		line-height:1.5em;
	}
	.sssmodulebox .sssmb_stext p {
		line-height:1.5em;
		padding:5px 0;
		text-indent:2em;
		margin:0;
		text-align:left;
	}
	.sssmodulebox .sssmb_stext ul {
		list-style:none;
		margin:1em 0 1em 0em;
	}
	.sssmodulebox .sssmb_stext ul li {
		display:list-item;
		margin:0;
		padding:5px 0 5px 0em;
		line-height:1.5em;
		background:none;
		text-align:left;
	}
	.sssmodulebox .sssmb_stext ul li:before {
		content:"";
		width:1em;
		height:0;
		border-top:1px solid #446b98;
		display:inline-block;
		position:relative;
		left: 0em;
		top: -3px;
		margin: 0 1.5em 0 0;
	}

	.sssmodulebox .yasharebox ul li {background:none; padding-left:0;}
	.sssmodulebox .sssmb_clr {
		clear:both;
		height:0;
		line-height:0;
		font-size:0;
		margin:0;
		padding:0;
	}
	
	.sssmodulebox .sssmb_articles {
	}
		.sssmodulebox .sssmb_articles .sssmba_itm {
			display: flex;
		}
			.sssmodulebox .sssmb_articles .sssmba_itm+.sssmba_itm {
				margin-top: 20px;
			}
		.sssmodulebox .sssmb_articles .sssmba_img {
			flex: 0 0 100px;
		}
			.sssmodulebox .sssmb_articles .sssmba_img img {
				max-width: 100%;
				border: 1px solid #ddd;
				border-radius: 3px;
				padding: 3px;
			}
		.sssmodulebox .sssmb_articles .sssmba_inf {
			flex: 1 1 auto;
			margin-left: 30px;
		}
		.sssmodulebox .sssmb_articles .sssmba_tit {
			font-size: 120%;
		}
		.sssmodulebox .sssmb_articles .sssmba_txt {
			margin-top: 13px;
		}
	
	.sssmodulebox .sssmb_tabs {
	}
		.sssmodulebox .sssmb_tabs .sssmbt_butts {
			display: flex;
			align-items: flex-end;
		}
		.sssmodulebox .sssmb_tabs .sssmbt_butt {
			border-top: 1px solid #ddd;
			border-left: 1px solid #ddd;
			background: #eee;
			padding: 10px 15px;
			margin-top: 10px;
			cursor: pointer;
			font-size: 120%;
			transition: .3s;
		}
		.sssmodulebox .sssmb_tabs .sssmbt_butt:nth-last-child(1) {
			border-right: 1px solid #ddd;
		}
		.sssmodulebox .sssmb_tabs .sssmbt_butt:nth-child(1) {
			border-radius: 5px 0 0 0;
		}
		.sssmodulebox .sssmb_tabs .sssmbt_butt:nth-last-child(1) {
			border-radius: 0 5px 0 0;
		}
		.sssmodulebox .sssmb_tabs .sssmbt_butt_a {
			background: #fff;
			padding-bottom: 20px;
			border-radius: 5px 5px 0 0 !important;
			margin-top: 0;
		}
		.sssmodulebox .sssmb_tabs .sssmbt_itms {
			border: 1px solid #ddd;
		}
		.sssmodulebox .sssmb_tabs .sssmbt_itm {
			position: absolute;
			top: 0px;
			left: 0px;
			right: 0px;
			z-index: 1;
			background: #fff;
			opacity: 0;
			visibility: hidden;
			padding: 0 35px;
		}
		.sssmodulebox .sssmb_tabs .sssmbt_itm_a {
			z-index: 2;
			opacity: 1;
			visibility: visible;
			position: relative;
			transition: .3s;
		}
		
	.sssmodulebox .sssmb_col {
		float:left;
		width:48%;
		box-sizing: border-box;
	}
	.sssmodulebox .sssmb_col_l {
		border-right: 1px solid #eee;
		padding-right:4%;
	}
	.sssmodulebox .sssmb_col_r {
		float:right;
	}
	.sssmodulebox .sssmb_img {
		float:left;
		margin-right:5%;
		margin-bottom:5%;
		line-height:0;
		position:relative;
	}
	.sssmodulebox .sssmb_ir {
		float:right;
		margin-right:0;
		margin-left:5%;
	}
	.sssmodulebox .sssmb_img2 {
		max-width:28%;
		float:left;
		margin-left:0;
		margin-right:5%;
	}
	.sssmodulebox .sssmb_img img {
		max-width:100%;
		margin:0;
		padding:0;
		border:none;
	}
	.sssmodulebox .sssmb_imgs {
		margin:0;
	}
	.sssmodulebox .sssmb_imgs_1 {
		margin-top:1em;
		margin-bottom:2em;
	}
	.sssmodulebox .sssmb_imgs .sssmb_img {
		margin-top:0;
		margin-bottom:0;
	}
	.sssmodulebox .sssmb_bck {
		position:absolute;
		top:0;
		left:0;
		width:100%;
		height:100%;
		padding:5px;
		box-sizing:border-box;
		line-height:0;
background: -moz-linear-gradient(top, rgba(0,25,48,0) 0%, rgba(0,22,43,0) 20%, rgba(0,12,22,0.7) 100%);
background: -webkit-linear-gradient(top, rgba(0,25,48,0) 0%,rgba(0,22,43,0) 20%,rgba(0,12,22,0.7) 100%);
background: linear-gradient(to bottom, rgba(0,25,48,0) 0%,rgba(0,22,43,0) 20%,rgba(0,12,22,0.7) 100%);
filter: progid:DXImageTransform.Microsoft.gradient( startColorstr=\'#00001930\', endColorstr=\'#b3000c16\',GradientType=0 );
	}
	.sssmodulebox .sssmb_ln {
		width:100%;
		height:100%;
		border:1px solid rgba(255,255,255,.7);
		box-sizing:border-box;
	}
	.sssmodulebox .sssmb_alt {
		position:absolute;
		bottom:0;
		left:0;
		width:100%;
		color:#fff;
		padding:0 18px 14px;
		font-size:90%;
		box-sizing:border-box;
		line-height:1.3em;
		text-align:left;
	}
	@media(max-width:600px){
		.sssmodulebox .sssmb_img {
			max-width:35%;
			float:none;
			margin-left:auto;
			margin-right:auto;
		}
	}
	@media(max-width:400px){
		.sssmodulebox .sssmb_img {
			max-width:50%;
		}
	}
	@media(max-width:350px){
		.sssmodulebox .sssmb_img {
			max-width:55%;
		}
	}
	@media(max-width:300px){
		.sssmodulebox .sssmb_img {
			max-width:70%;
		}
	}
</style>';
