<?php

	session_set_cookie_params( 60*60*24 );
	session_start();
	
	error_reporting( 0 );

	
	
	$table1= 'customer AS c';
	$table2= 'website_satellit AS ws';
	$table3= 'website_param AS wp';
	$table4= 'fn_delta';
	$table5= 'custmworks AS cw';
	$table6= 'workers AS w';
	$table8= 'extracts AS e';
	$table9= 'reports AS r';
	
	$table10= 'mh_domain AS dd';
	$table11= 'mh_poddomain AS pdd';
	
	$b3= "buran__001";
	
	
	$dbh= 'mysql.c334652.myjino.ru';
	$dbu= '045586894_antivi';
	$dbp= '0jqOxQHo2_s7';
	$dbn= 'c334652_antivir';
	$dbm= 'SET NAMES';
	$dbc= 'utf8';
	$dbq= '';
	
	
	$root= $_SERVER[ 'DOCUMENT_ROOT' ];
	$root= '/home/u333528/bunker-yug.ru/www';
	
	@include_once( $root ."/db.php" );
	@include_once( $root ."/idn/idna_convert.class.php" );
	
	
	$idn= new idna_convert();
	
//=======================================================================

	?>
	
	<h1>Проверка на вирусы</h1>
	
	<form action="" method="get">
		<input type="text" name="domain" value="<?= $_GET[ 'domain' ] ?>" />
		<input type="submit" value="Вывести ссылку" />
	</form>
	
	
	<?php
	if( isset( $_GET[ 'domain' ] ) )
	{
		$url= site_from_url( trim( $_GET[ 'domain' ] ) );
		$url2= $idn->encode( $url );
		$w= @file_get_contents( "http://fndelta.gavrishkin.ru/__password__002.php?host={$url2}&pp=53_6EeSQsBK9_gWoZ8Uf" );
		print '<a target="_blank" href="http://'. $url .'/">www.'. $url .'</a><br />';
		print "<a target='_blank' href='http://{$url2}/_buran/__001.php?act=antivirus&w={$w}&print&info&h={$dbh}&u={$dbu}&p={$dbp}&n={$dbn}&scheme'>Перейти на сайт для проверки на вирусы</a><br /><br />";
		
		print "<a target='_blank' href='http://{$url2}/_buran/__005.php?act=archive_files&w={$w}'>Архивировать файлы</a><br />";
		print "<a target='_blank' href='http://{$url2}/_buran/__005.php?act=archive_db&w={$w}'>Архивировать базу данных</a><br />";
		
		print '<br />';
		
		print "<a target='_blank' href='http://{$url2}/_buran/__008.php?act=etalon&w={$w}'>Проверка по эталону</a><br />";
		
		print '<br /><br />';
		
		print "<a target='_blank' href='http://{$url2}/_buran/__002.php?act=unblockedadmin&w={$w}'>Разблокировать админа</a><br />";
		
		print '<br /><br />';
		
		print 'Обновление модулей:<br />';
		print "<a target='_blank' href='http://{$url2}/_buran/__004.php?w={$w}&act=update&fromf=__001&tof=__001.php'>Антивирус</a><br />";
		print "<a target='_blank' href='http://{$url2}/_buran/__004.php?w={$w}&act=update&fromf=__002&tof=__002.php'>Работа с паролями</a><br />";
		print "<a target='_blank' href='http://{$url2}/_buran/__004.php?w={$w}&act=update&fromf=__003&tof=__003.php'>Состояние файлов</a><br />";
		print "<a target='_blank' href='http://{$url2}/_buran/__004.php?w={$w}&act=update&fromf=__004&tof=__004.php'>Обновлялка</a><br />";
		print "<a target='_blank' href='http://{$url2}/_buran/__004.php?w={$w}&act=update&fromf=__005&tof=__005.php'>Архиватор</a><br />";
		print "<a target='_blank' href='http://{$url2}/_buran/__004.php?w={$w}&act=update&fromf=__008&tof=__008.php'>Эталоны</a><br />";
		
		print '<br /><br />';
	}
	
	
	if( $_GET[ 'act' ] == 'pereprov' )
	{
		$id= intval( $_GET[ 'id' ] );
		if( $id )
		{
			mysql_query( "UPDATE {$b3} SET last=CONCAT( '[[PEREPROVERKA]]\n', last ), dt=0 WHERE id={$id} LIMIT 1" );
		}
		exit();
	}
	
	
	$rr= mysql_query( "SELECT b3.* FROM {$b3} AS b3
		LEFT JOIN {$table1} ON c.id=b3.idc
			WHERE b3.last NOT LIKE '[[START%' || b3.last LIKE '%[[VIRUS]]%' ORDER BY IF( b3.last NOT LIKE '[[START%', 1, 0 ), dt DESC" );

	if( $rr && mysql_num_rows( $rr ) > 0 )
	{
		print '<table width="100%" border="1" cellpadding="5" cellspacing="0">';
		
		$ii= 0;
		while( $row= mysql_fetch_assoc( $rr ) )
		{
			$ii++;
			
			$url= site_from_url( trim( $row[ 'domain' ] ) );
			$url2= $idn->encode( $url );
			
			if( ! $_SESSION[ date( 'Y-m-d-H' ) ][ 'w' ][ $url2 ] )
			{
				$w= @file_get_contents( "http://fndelta.gavrishkin.ru/__password__002.php?host={$url2}&pp=53_6EeSQsBK9_gWoZ8Uf" );
				$_SESSION[ date( 'Y-m-d' ) ][ 'w' ][ $url2 ]= $w;
			}else{
				$w= $_SESSION[ date( 'Y-m-d-H' ) ][ 'w' ][ $url2 ];
			}
			
			if( ! strstr( $row[ 'last' ], '[[START]]' ) )
			{
				$row[ 'last' ]= substr( $row[ 'last' ], 0, 100 );
			}
			
			if( $ii == 1 ) print '<tr>';
			print '<td>';
				if( $row[ 'idc' ] ) print '<a target="_blank" href="http://bunker-yug.ru/customer.php?p=edit&id='. $row[ 'idc' ] .'">карточка ID '. $row[ 'idc' ] .'</a>';
			print '</td>';
			print '<td>'. date( 'd.m.Y, H:i', $row[ 'dt' ] ) .'<br /><a target="_blank" href="http://'. $url .'/">'. $url .'</a><br /><br />
				<a target="_blank" href="'. "http://{$url2}/_buran/__001.php?act=antivirus&w={$w}&print&info&h={$dbh}&u={$dbu}&p={$dbp}&n={$dbn}&scheme" .'">Проверить на сайте</a><br /><br />
				<a href="/__buran/page/001.php?act=pereprov&id='. $row[ 'id' ] .'">Перепроверить</a><br /><br />
			</td>';
			print '<td><pre>'. htmlspecialchars( $row[ 'last' ] ) .'</pre></td>';
			if( $ii == 2 ) print '</tr>';
			if( $ii == 2 ) $ii= 0;
		}
		
		print '</table>';
	}


//=======================================================================

function site_from_url( $str )
{
	$str= trim( $str );
	$str= strtolower( $str );
	$str= str_replace( "https://www.", '', $str );
	$str= str_replace( "https://", '', $str );
	$str= str_replace( "http://www.", '', $str );
	$str= str_replace( "http://", '', $str );
	$str= str_replace( "www.", "", $str );
	$str= str_replace( "//", "/", $str );
	$str= explode( "/", $str );
	$str= explode( ":", $str[0] );
	$str= str_replace( "/", '', $str[0] );
	$str= str_replace( " ", '', $str );
	$str= str_replace( ",", '', $str );
	$str= str_replace( ";", '', $str );
	$str= trim( $str );
	
	return $str;
}