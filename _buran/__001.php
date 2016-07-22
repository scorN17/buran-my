<?php
// Buran_001
// scorN - v.7.7
// 22.07.2016
// Буран
//====================================================================================================
	//error_reporting( E_ALL & ~E_NOTICE & ~E_WARNING );
	error_reporting( E_ALL & ~E_NOTICE );
	ini_set( 'display_errors', 'On' );
                                                                                                    
                                                                                                    
                                                                                                    
                                                                                                    
                                                                                                    
                                                                                                    
                                                                                                    
                                                                                                    
                                                                                                 
//====================================================================================================
	define( '_DS', DIRECTORY_SEPARATOR );
	$host= str_replace( "www.", "", $_SERVER[ 'HTTP_HOST' ] );
	$root= __FILE__;
	$scriptname= $_SERVER[ 'SCRIPT_NAME' ];
	if( strpos( $root, "\\" ) !== false ) $scriptname= str_replace( "/", "\\", $scriptname );
	$root= str_replace( $scriptname, '', $root );
	if( substr( $root, strlen( $root )-1, 1 ) == _DS ) $root= substr( $root, 0, -1 );
	if( isset( $_GET[ 'dir' ] ) ) $dir= trim( urldecode( $_GET[ 'dir' ] ) ); else $dir= '';
		if( substr( $dir, 0, 1 ) != _DS ) $dir= _DS . $dir;
		if( substr( $dir, strlen( $dir )-1, 1 ) == _DS ) $dir= substr( $dir, 0, -1 );
	$root_dir= $root . $dir;
	
	$ww= @file_get_contents( "http://fndelta.gavrishkin.ru/__password__002.php?host=". $host ."&w=". $_GET[ 'w' ] );
	if( ! $ww || $_GET[ 'w' ] == '' || $_GET[ 'w' ] != $ww ){ print '[[FAIL]]'; exit(); }
//====================================================================================================

	$c_starttime= microtime( 1 );
	
	$act= $_GET[ 'act' ];
	$maxtime= ( isset( $_GET[ 'maxtime' ] ) ? intval( $_GET[ 'maxtime' ] ) : 29 );
	$lvl= ( isset( $_GET[ 'lvl' ] ) ? intval( $_GET[ 'lvl' ] ) : 0 );
	$maxlvl= ( isset( $_GET[ 'maxlvl' ] ) ? intval( $_GET[ 'maxlvl' ] ) : 999 );
	$ignorelvl= ( isset( $_GET[ 'ignorelvl' ] ) ? intval( $_GET[ 'ignorelvl' ] ) : 0 );
	$files= ( isset( $_GET[ 'files' ] ) ? $_GET[ 'files' ] : '/php/js/htaccess/html/htm/suspected/' );
	$ignore= ( isset( $_GET[ 'ignore' ] ) ? urldecode( $_GET[ 'ignore' ] ) : '' );
	$file= ( isset( $_GET[ 'file' ] ) ? urldecode( $_GET[ 'file' ] ) : '' );
	$tm= ( isset( $_GET[ 'tm' ] ) ? intval( $_GET[ 'tm' ] ) : 0 );
	$tm= ( ! $tm ? 0 : ( $tm > 0 && $tm < 370 ? time() - ( $tm * 60*60*24 ) : $tm ) );
	$autodeletefilesize= ( isset( $_GET[ 'adfs' ] ) ? intval( $_GET[ 'adfs' ] ) : false );
	$autodelete= ( isset( $_GET[ 'autodelete' ] ) ? true : false );
	$autocurefile= ( isset( $_GET[ 'autocurefile' ] ) ? true : false );
	$ignoreignore= ( isset( $_GET[ 'ignoreignore' ] ) ? true : false );
	$filerename= ( isset( $_GET[ 'filerename' ] ) ? urldecode( $_GET[ 'filerename' ] ) : false );
	$scheme= ( isset( $_GET[ 'scheme' ] ) ? true : false );
	
	$rn= "\n";
	$br= "<br>";
	
/* db */
	if( isset( $_GET[ 'h' ] ) ) $db_host= addslashes( trim( $_GET[ 'h' ] ) );
	if( isset( $_GET[ 'u' ] ) ) $db_user= addslashes( trim( $_GET[ 'u' ] ) );
	if( isset( $_GET[ 'p' ] ) ) $db_passw= addslashes( trim( $_GET[ 'p' ] ) );
	if( isset( $_GET[ 'n' ] ) ) $db_name= addslashes( trim( $_GET[ 'n' ] ) ); else $db_name= $db_user;
	if( isset( $_GET[ 'm' ] ) ) $db_conn_method= addslashes( trim( urldecode( $_GET[ 'm' ] ) ) ); else $db_conn_method= 'SET NAMES';
	if( isset( $_GET[ 'c' ] ) ) $db_conn_charset= addslashes( trim( $_GET[ 'c' ] ) ); else $db_conn_charset= 'utf8';
	if( isset( $_GET[ 'q' ] ) ) $db_query= urldecode( $_GET[ 'q' ] );
/* db */
//====================================================================================================

	$tpl_item= '<div class="line">
		<div class="print float1 bord1"><a target="_blank" href="?act=printfile&w='. $_GET[ 'w' ] .'&dir='. $dir .'&file=[[fullpath]]">print</a></div>
		<div class="print float1 bord1"><a target="_blank" href="?act=ignorefile&w='. $_GET[ 'w' ] .'&h='. $_GET[ 'h' ] .'&u='. $_GET[ 'u' ] .'&p='. $_GET[ 'p' ] .'&n='. $_GET[ 'n' ] .'&m='. $_GET[ 'm' ] .'&c='. $_GET[ 'c' ] .'&dir='. $dir .'&file=[[fullpath]]&signid=[[signatureid]]&strpos=[[strpos]]">ignore</a></div>
		<div class="size float1 bord1">[[filesize]]</div>
		<div class="date float1 bord1">[[filedate]]</div>
		<div class="type float1 bord1">[[type]]</div>
		<div class="dir float1 bord1">[[directory]]</div>
		<div class="file float1 bord1">[[file]]</div>
		<div class="clr">&nbsp;</div>
	</div>';
	
	$tpl_css= '<style  type="text/css">
div { font-family:Arial;font-size:12px;height:15px; }
.clr { clear:both;font-size:0;line-height:0;height:0; }
.line { height:auto; }
.line:hover { background:#f0f0f0; }
.float1 { float:left; padding: 2px 0; }
.bord1 { border-bottom:1px solid #eee; }
.type { width:250px; }
.dir { width:500px; text-align:right; }
.file { padding-left:20px; }
.print { width:55px; } .print a { font-size:10px; }
.size { width:110px;font-size:10px;color:#666; }
.date { width:140px;font-size:10px;color:#666; }
</style>';

	$tpl_time= '<div>-time</div>';
	$tpl_err_opendir= '<div>-err_opendir: [[directory]]</div>';
	$tpl_maxfiles= '<div>-maxfiles: [[directory]]</div>';
	$tpl_maxlvl= '<div>-maxlvl: [[directory]]</div>';
	$tpl_filesize= '<div>-filesize: [[file]]</div>';
	
	$b= "\n";
//====================================================================================================

if( $act == 'antivirus' )
{
	mysql_connect( $db_host, $db_user, $db_passw, $db_name );
	$mysql_select_db_result= mysql_select_db( $db_name );
	mysql_query( "{$db_conn_method} {$db_conn_charset}" );
	if( ! $mysql_select_db_result ) exit();
	
	$count_files= 0;
	$count_checkedfiles= 0;
	
	$r_kws= mysql_query( "SELECT * FROM delta_v_search WHERE ".( $db_query != '' ? $db_query : 'flag_off=0' )." ORDER BY IF( type='virus', 1, IF( type='simple', 2, 3 ) ), id DESC" );
	
	$cms= cms();
	
	$signatureinfo= '';
	$print_1 .= '<div>-host: <a target="_blank" href="http://'. $host .'/">'. $host .'</a></div>';
	$print_1 .= '<div>-dir: '. $root_dir .'</div>';
	$print_1 .= '<div>-cs: '. mysql_num_rows( $r_kws ) .'</div>';
	if( $cms[ 0 ] == 'modx_evo' ) $print_1 .= '<div>-modx: '. $cms[ 2 ] .'</div>';
	$print_2 .= '[[START]]' .$b;
	$print_2 .= $host .$b;
	$print_2 .= '[[DIR_'. $root_dir .']]' .$b;
	$print_2 .= '[[CS_'. mysql_num_rows( $r_kws ) .']]' .$b;
	if( $cms[ 0 ] == 'modx_evo' ) $print_2 .= '[[MODX_'. $cms[ 2 ] .']]' .$b;
	if( $r_kws && mysql_num_rows( $r_kws ) > 0 )
	{
		$flag_print[ 'maxlvl' ]= false;
		$flag_print[ 'maxfiles' ]= false;
		$flag_print[ 'maxtime' ]= false;
		$flag_print[ 'virus' ]= false;
		$level_of_directory= 1;
		$print_1 .= '<div>-start</div>';
		
		$print_1 .= buran( "", $fileslist );
		
		if( $scheme )
		{
			$WW= '<wrapper>
				<files>
					<!--div class="folder_name">Корень</div-->
					<div class="folder_files"><span>&nbsp;</span>[['._DS.']]<div class="clr"></div></div>
				</files>
				<subm>{{'._DS.'}}</sub>
				<div class="clr"></div>
			</wrapper>';
			if( $fileslist )
			{
				foreach( $fileslist AS $key => $row )
				{
					if( strpos( $fileslist[ _DS ][ 'folders' ][ 'str' ], '|'.$key ) === false ) continue;
					
					$key2= substr( $key, 0, -1 );
					$parent= substr( $key2, 0, strrpos( $key2, _DS )+1 );
					$folder_name= substr( $key2, strrpos( $key2, _DS )+1 );
					
					$tmp= '<wrapper>
						<files>
							<!--div class="folder_name" title="'. $key .'">'. $folder_name .'</div-->
							<div class="folder_files"><span title="'. $key .'">&nbsp;</span>[['. $key .']]<div class="clr"></div></div>
						</files>
						<subm>{{'. $key .'}}</sub>
						<div class="clr"></div>
					</wrapper>';
					$WW= str_replace( '{{'. $parent .'}}', $tmp.'{{'. $parent .'}}', $WW );
					
					$tmp= '';
					if( $row[ 'files' ] )
					{
						foreach( $row[ 'files' ] AS $key2 => $row2 )
						{
							$color= false;
							if( $row2[ 'type' ] == 'suspected' ) $color= substr( md5( $row2[ 'id' ] ), 0, 6 );
							$tmp .= '<a class="'. $row2[ 'type' ] .'" '.( $color ? 'style="background:#'. $color .';"' : '' ).' target="_blank" href="?act=printfile&w='. $_GET[ 'w' ] .'&dir='. $dir .'&file='. $key . $key2 .'" title="'. $key2 .'">'. $row2[ 'id' ] .'</a>';
						}
					}
					$WW= str_replace( '[['. $key .']]', $tmp, $WW );
				}
				$WW= preg_replace( "/\{\{(.*)\}\}/", '', $WW );
			}
			print $WW;
		}
		
		$print_1 .= '<div>-end</div>';
		$print_1 .= '<div>-cf: '. $count_checkedfiles .' / '. $count_files .'</div>';
		$print_2 .= '[[CF_'. $count_checkedfiles .'_'. $count_files .']]' .$b;
	}else{
		$print_1 .= '<div>-no_signature</div>';
		$print_2 .= '[[NO_SIGNATURE]]' .$b;
	}
	$print_2 .= '[[END]]' .$b;
	$mt_end= microtime( 1 );
	$mt= round( $mt_end - $c_starttime, 3 );
	$print_1 .= '<div>-p'. $mt .'</div>';
	$print_1 .= '<div>-v7.5</div>';
	$print_1 .= '<div>-d'. date( 'Y.m.d.H.i' ) .'</div>';
	$print_2 .= '[[P_' . $mt .']]' .$b;
	$print_2 .= '[[V_7.5]]' .$b;
	$print_2 .= '[[D_'. date( 'Y.m.d.H.i' ) .']]' .$b;
	
	mysql_close();
}

if( $act == 'ignorefile' && $file != '' )
{
	$signid= intval( $_GET[ 'signid' ] );
	$strpos= addslashes( trim( $_GET[ 'strpos' ] ) );
	$file= addslashes( $file );
	if( $signid && $strpos )
	{
		mysql_connect( $db_host, $db_user, $db_passw, $db_name );
		$mysql_select_db_result= mysql_select_db( $db_name );
		mysql_query( "{$db_conn_method} {$db_conn_charset}" );
		if( ! $mysql_select_db_result ){ print 'ERROR-2'; exit(); }
		$rr= mysql_query( "INSERT INTO delta_v_ignore_file_signature SET file='{$file}', signature='{$signid}', kws='{$strpos}', dth='".date( 'Y.m.d, H:i' )."'" );
		if( $rr ) print 'OK'; else print 'ERROR-3';
	}else{
		print 'ERROR-1';
	}
}
if( $act == 'editfile' && $file != '' )
{
	$path= get_url_bez_tochek( $root_dir ._DS. $file );
	if( isset( $_POST[ 'save' ] ) )
	{
		$content= $_POST[ 'codemirror' ];
		if( get_magic_quotes_gpc() ) $content = stripslashes( $content );
		$fp= fopen( $path, 'w' );
		if( $fp )
		{
			fwrite( $fp, $content );
			fclose( $fp );
		}
	}
	$fp= fopen( $path, 'r' );
	if( $fp )
	{
		$content= '';
		while( ! feof( $fp ) )
		{
			$content .= fread( $fp, 1024*1024 );
		}
		if( $content )
		{
			$content= htmlentities( $content, ENT_SUBSTITUTE );
			//$content= htmlspecialchars( $content );
			//$content= mb_encode_numericentity( $content );
			
			print '<!DOCTYPE html><html><head>';
			if( is_dir( $root .'/assets/plugins/codemirror/' ) )
			{
				print '<link rel="stylesheet" href="../assets/plugins/codemirror/cm/lib/codemirror.css">
					<link rel="stylesheet" href="../assets/plugins/codemirror/cm/theme/default.css">
					<script src="../assets/plugins/codemirror/cm/lib/codemirror-compressed.js"></script>
					<script src="../assets/plugins/codemirror/cm/addon-compressed.js"></script>
					<!-- script src="../assets/plugins/codemirror/cm/mode/htmlmixed-compressed.js"></script -->
					<script src="../assets/plugins/codemirror/cm/mode/php-compressed.js"></script>
					<script src="../assets/plugins/codemirror/cm/emmet-compressed.js"></script>
					<script src="../assets/plugins/codemirror/cm/search-compressed.js"></script>
				';
			}
			print '</head><body>';
			
			print '<form action="" method="post">';
				print '<input type="submit" name="save" value="Сохранить" /><br /><br />';
				print '<div style="height:auto;"><textarea id="CodeMirror" name="codemirror" style="width:100%;height:500px;">'. $content .'</textarea></div>';
			print '</form>';
			
			if( is_dir( $root .'/assets/plugins/codemirror/' ) )
			{
				print '<script>
					var myCodeMirror = CodeMirror.fromTextArea(document.getElementById("CodeMirror"), {
						mode: "application/x-httpd-php",
						theme: "default",
						indentUnit: 6,
						tabSize: 6,
						lineNumbers: true,
						matchBrackets: true,
						lineWrapping: true,
						gutters: ["CodeMirror-linenumbers", "breakpoints"],
						styleActiveLine: false,
						indentWithTabs: true,
						viewportMargin: Infinity
					});
				</script>';
			}
			print '</body></html>';
		}
		fclose( $fp );
	}
	exit();
}
if( $act == 'filerename' && $file != '' && $filerename )
{
	if( rename( $root_dir ._DS. $file, $filerename ) ) print '[[OK]]'; else print '[[ERROR]]';
}
if( $act == 'printfile' && $file != '' )
{
	print date( 'd-m-Y, H:i:s', filectime( $root_dir ._DS. $file ) );
	print '<br />';
	print '<a href="?act=editfile&w='. $_GET[ 'w' ] .'&dir='. $dir .'&file='. $file .'">EDIT</a>';
	print '<br />';
	print '<a href="?act=curefile&w='. $_GET[ 'w' ] .'&dir='. $dir .'&file='. $file .'">CURE</a>';
	print '<br />';
	print '<a href="?act=chmod&chmod=0775&w='. $_GET[ 'w' ] .'&dir='. $dir .'&file='. $file .'">0775</a>';
	print '<br /><br /><hr />';
	print '<a href="?act=deletefile&w='. $_GET[ 'w' ] .'&dir='. $dir .'&file='. $file .'">DELETE</a>';
	print '<br /><br /><br /><hr />';
	print '<div style="width:100%;word-wrap:break-word;">';
	highlight_file( $root_dir ._DS. $file );
	print '</div>';
	print '<hr>';
}
if( $act == 'deletefile' && $file != '' )
{
	if( isset( $_GET[ 'gogo' ] ) ) unlink( $root_dir ._DS. $file );
		else print '<a href="?gogo&act=deletefile&w='. $_GET[ 'w' ] .'&dir='. $dir .'&file='. $file .'">DELETE</a>';
}
if( $act == 'curefile' && $file != '' )
{
	curefile( $file );
	exit();
}
if( $act == 'chmod' && $file != '' )
{
	if( chmod( $root_dir . $file, ( $_GET[ 'chmod' ] ? $_GET[ 'chmod' ] : 0755 ) ) ) print '[[OK]]'; else print '[[ERROR]]';
	exit();
}
function curefile( $file )
{
	global $dir;
	global $root_dir;
	global $autocurefile;
	
	$path= get_url_bez_tochek( $root_dir ._DS. $file );
	
	if( $autocurefile ) print $path;print '<br />';
	
	$fp= fopen( $path, 'r' );
	if( $fp )
	{
		$content= '';
		while( ! feof( $fp ) )
		{
			$content .= fread( $fp, 1024*1024 );
		}
		fclose( $fp );
		if( $content )
		{
			$masks[]= "/^<\?php[ ]{230,280}(.*)\?>[ ]{1}/Umi";
			$masks[]= "/^<\?php[ ]{230,600}(.*)\?>\\r\\n/Umi";
			$masks[]= "/^<\?php[ ]{230,600}(.*)\?>/Umi";
			$masks[]= "/include(.*)\"(\\\x[0-9]{1,2}(.*)){3}\";/Umis";
			$masks[]= "/\r\n\r\n(.*)\\\$_COOKIE[ ]{0,5}\[(.*)\][ ]{0,5}\([ ]{0,5}\\\$_COOKIE[ ]{0,5}\[(.*)\r\n\r\n/Umis";
			$masks[]= "/(\r\n){1,5}(<\?php)(.*)assert(.*)\?>/Umis";
			$masks[]= "/(\n){0,5}if \((.*)assert(.*)base64_decode(.*)exit\(\);(.*)\}(\n){1,5}/Umis";
			foreach( $masks AS $mask )
			{
				preg_match_all( $mask, $content, $result );
				if( ! empty( $result[ 0 ][ 0 ] ) )
				{
					if( ! $autocurefile )
					{
						if( ! isset( $_GET[ 'gogo' ] ) )
						{
							print '<a href="?gogo&act=curefile&w='. $_GET[ 'w' ] .'&dir='. $dir .'&file='. $file .'">CURE</a><hr />';
						}
						print '<pre>';
						print_r( $result );
						print '</pre>';
					}
					
					$content= str_ireplace( $result[ 0 ][ 0 ], '', $content );
					
					if( ! $autocurefile )
					{
						print '<pre>';
						print_r( $content );
						print '</pre>';
					}
					
					if( true && isset( $_GET[ 'gogo' ] ) )
					{
						$fp= fopen( $path, 'w' );
						if( $fp )
						{
							fwrite( $fp, $content );
							fclose( $fp );
						}
						continue;
					}
				}
			}
		}
	}
}

//====================================================================================================
if( isset( $_GET[ 'info' ] ) )
{
	$print_1 .= '<br />';
	$print_1 .= $signatureinfo;
	$print_1 .= '<br />';
	$print_1 .= '<div>---get---</div>';
	$print_1 .= '<div>&act=antivirus</div>';
	$print_1 .= '<div>&print</div>';
	$print_1 .= '<div>&dir=/</div>';
	$print_1 .= '<div>&maxtime=29</div>';
	$print_1 .= '<div>&lvl=0</div>';
	$print_1 .= '<div>&maxlvl=999</div>';
	$print_1 .= '<div>&ignorelvl=0</div>';
	$print_1 .= '<div>&maxfiles=99999</div>';
	$print_1 .= '<div>&files=/php/js/htaccess/html/htm/suspected/</div>';
	$print_1 .= '<div>&ignore=/folder1/folder2/file1.php/</div>';
	$print_1 .= '<div>&tm=0</div>';
	$print_1 .= '<div>&autodelete</div>';
	$print_1 .= '<div>&adfs - autodeletefilesize</div>';
	$print_1 .= '<div>&autocurefile</div>';
	$print_1 .= '<div>&ignoreignore</div>';
	$print_1 .= '<div>&filerename=new_file_name.php</div>';
	$print_1 .= '<div>&etalon</div>';
	$print_1 .= '<div>&scheme</div>';
	
	$print_1 .= '<br />';
	$print_1 .= '<div>---db---</div>';
	$print_1 .= '<div>&h=host</div>';
	$print_1 .= '<div>&u=user</div>';
	$print_1 .= '<div>&p=password</div>';
	$print_1 .= '<div>&n=name</div>';
	$print_1 .= '<div>&m=method</div>';
	$print_1 .= '<div>&c=charset</div>';
	$print_1 .= '<div>&q=query</div>';
}

if( isset( $_GET[ 'print' ] ) )
{
	print $tpl_css;
?>

<style type="text/css">
	wrapper {
		display: table;
		width: 100%;
	}
		files {
			display: table-cell;
			vertical-align: top;
			width: 290px;
		}
			files .folder_name {
				background: #f0f0f0;
				text-align: center;
				font-weight: bold;
				padding: 0px 5px;
				font-size: 11px;
			}
			files .folder_files {
			}
				files .folder_files >span {
					display: block;
					float: left;
					width: 20px;
					height: 12px;
					margin: 0px 1px 1px 0px;
					border: 1px solid #ccc;
					cursor: default;
				}
				files .folder_files >a {
					display: block;
					float: left;
					width: 22px;
					height: 14px;
					cursor: pointer;
					background: #000;
					margin: 0px 1px 1px 0px;
					text-decoration: none;
					font-size: 8px;
					color: #fff;
					line-height: 15px;
					text-align: center;
					overflow: hidden;
				}
				files .folder_files .virus {
					width: 45px;
				}
				files .folder_files .suspected {
					background: #fce86c;
					color: #d5ba12;
					color: #fff;
				}
				files .folder_files .simple {
					background: #f5f5f5;
					color: #ccc;
				}
		subm {
			display: table-cell;
			vertical-align: top;
		}
</style>

<?php
	print $print_1;
}else{
	print $print_2;
}
//====================================================================================================

function buran( $folder, &$fileslist )
{
	global $root;
	global $dir;
	global $root_dir;
	global $subdir;
	global $c_starttime;
	global $maxtime;
	global $files;
	global $flag_print;
	global $count_files;
	global $count_checkedfiles;
	global $tm;
	global $tpl_item;
	global $tpl_err_opendir;
	global $r_kws;
	global $print_1;
	global $print_2;
	global $signatureinfo;
	global $signatureinfoflag;
	global $lvl;
	global $maxlvl;
	global $ignorelvl;
	global $maxfiles;
	global $tpl_maxfiles;
	global $tpl_maxlvl;
	global $tpl_filesize;
	global $b;
	global $ignore;
	global $level_of_directory;
	global $autodeletefilesize;
	global $autodelete;
	global $autocurefile;
	global $ignoreignore;
	
	if( ! $folder ) $folder= _DS;
	
	$count_file_in_directory= 1;
	$flag_print[ 'max_files_this_directory' ]= false;
	$flag_print[ 'max_level_directrory_this_directory' ]= false;
	
	if( $open= opendir( $root_dir . $folder ) )
	{
		$fileslist[ $folder ]= array();
		
		while( $file= readdir( $open ) )
		{
			$mt_end= microtime( 1 );
			if( $mt_end - $c_starttime > $maxtime && $maxtime != '' )
			{
				if( ! $flag_print[ 'maxtime' ] )
				{
					$print_1 .= '<div>-maxtime</div>';
					$print_2 .= '[[TIME]]' .$b;
					$flag_print[ 'maxtime' ]= true;
				}
				break;
			}
			if( ! is_dir( $root_dir . $folder . $file ) )
			{
				if( $ignorelvl && $level_of_directory <= $ignorelvl ) continue;
				if( $lvl && $level_of_directory != $lvl ) continue;
				
				$count_files++;
				if( $count_file_in_directory > $maxfiles && $maxfiles != '' )
				{
					if( ! $flag_print[ 'max_files_this_directory' ] )
					{
						$tmp= str_replace( "[[directory]]", $folder, $tpl_maxfiles );
						$print_1 .= $tmp;
						$flag_print[ 'max_files_this_directory' ]= true;
					}
					if( ! $flag_print[ 'maxfiles' ] )
					{
						$print_2 .= '[[MAXFILES]]' .$b;
						$flag_print[ 'maxfiles' ]= true;
					}
					continue;
				}
				$count_file_in_directory += 1;
				$tmp3= substr( strrchr( $file, "." ), 1 );
				if( ( stripos( $files, '/'. $tmp3 .'/' ) !== false || $files == '' ) && stripos( $ignore, '/'. $root_dir . $folder . $file .'/' ) === false )
				{
					$count_checkedfiles++;
					$tmp_filesize= filesize( $root_dir . $folder . $file );
					if( $tmp_filesize > 0 )
					{
						$simplelineflag= false;
						$o= @fopen( $root_dir . $folder . $file, "rb" );
						if( ! $o )
						{
							$simplelineflag= true;
						}else{
							$r= fread( $o, $tmp_filesize );
							for( $kk=0; $kk < mysql_num_rows( $r_kws ); $kk++ )
							{
								$kws_rassh= mysql_result( $r_kws, $kk, 'rassh' );
								if( ! empty( $kws_rassh ) && stripos( $kws_rassh, '/'. $tmp3 .'/' ) === false ) continue;
								
								$tmp2= 'not';
								$kw1= $kw2= $kw3= $kw4= 0;
								$regexp= ( mysql_result( $r_kws, $kk, 'regexp' ) );
								$result= null;
								if( $regexp ) preg_match( $regexp, $r, $result );
								if( ! $regexp || count( $result[ 0 ] ) >= 1 )
								{
									$kw1= stripos( $r, mysql_result( $r_kws, $kk, 'kw_1' ) );
									if( ( mysql_result( $r_kws, $kk, 'kw_1' ) == '-' || $kw1 !== false ) && mysql_result( $r_kws, $kk, 'type' ) != 'antivirus' )
									{
										$kw2= stripos( $r, mysql_result( $r_kws, $kk, 'kw_2' ), $kw1 );
										if( ( mysql_result( $r_kws, $kk, 'kw_2' ) == '-' || $kw2 !== false ) && mysql_result( $r_kws, $kk, 'type' ) != 'antivirus' )
										{
											$kw3= stripos( $r, mysql_result( $r_kws, $kk, 'kw_3' ), $kw2 );
											if( ( mysql_result( $r_kws, $kk, 'kw_3' ) == '-' || $kw3 !== false ) && mysql_result( $r_kws, $kk, 'type' ) != 'antivirus' )
											{
												$kw4= stripos( $r, mysql_result( $r_kws, $kk, 'kw_4' ), $kw3 );
												if( ( mysql_result( $r_kws, $kk, 'kw_4' ) == '-' || $kw4 !== false ) && mysql_result( $r_kws, $kk, 'type' ) != 'antivirus' )
												{
													if( true /*|| mysql_result( $r_kws, $kk, 'ignore' ) == '-' || ( mysql_result( $r_kws, $kk, 'ignore' ) != '-' && stripos( $r, mysql_result( $r_kws, $kk, 'ignore' ) ) === false )*/ )
													{
														if( mysql_result( $r_kws, $kk, 'size' ) == 0 || ( mysql_result( $r_kws, $kk, 'size' ) > 0 && mysql_result( $r_kws, $kk, 'size' ) == $tmp_filesize ) )
														{
															$rr= mysql_query( "SELECT * FROM delta_v_ignore_file_signature WHERE file='". $folder . $file ."' AND signature=". mysql_result( $r_kws, $kk, 'id' ) ." AND kws='{$kw1}-{$kw2}-{$kw3}-{$kw4}' LIMIT 1" );
															if( $ignoreignore || ( $rr && mysql_num_rows( $rr ) == 0 ) )
															{
																$tmp2= 'yea';
																
																if( ! $signatureinfoflag[ mysql_result( $r_kws, $kk, 'id' ) ] )
																{
																	$signatureinfoflag[ mysql_result( $r_kws, $kk, 'id' ) ]= true;
																	$signatureinfo .= '<div><pre>'. mysql_result( $r_kws, $kk, 'id' ) .'. '. mysql_result( $r_kws, $kk, 'name' ) .' |||||| '. htmlspecialchars( mysql_result( $r_kws, $kk, 'kw_1' ) ) .' |||||| '. htmlspecialchars( mysql_result( $r_kws, $kk, 'kw_2' ) ) .' |||||| '. htmlspecialchars( mysql_result( $r_kws, $kk, 'kw_3' ) ) .' |||||| '. htmlspecialchars( mysql_result( $r_kws, $kk, 'kw_4' ) ) .' |||||| '. htmlspecialchars( mysql_result( $r_kws, $kk, 'regexp' ) ) .' |||||| size-'. mysql_result( $r_kws, $kk, 'size' ) .'</pre></div>';
																}
															}
														}
													}
												}elseif( $kw4 === false && mysql_result( $r_kws, $kk, 'type' ) == 'antivirus' ){
													$tmp2= 'yea';
												}
											}elseif( $kw3 === false && mysql_result( $r_kws, $kk, 'type' ) == 'antivirus' ){
												$tmp2= 'yea';
											}
										}elseif( $kw2 === false && mysql_result( $r_kws, $kk, 'type' ) == 'antivirus' ){
											$tmp2= 'yea';
										}
									}elseif( $kw1 === false && mysql_result( $r_kws, $kk, 'type' ) == 'antivirus' ){
										$tmp2= 'yea';
									}
								}
								
								$tpl_item_type= mysql_result( $r_kws, $kk, 'id' ) .'. '. mysql_result( $r_kws, $kk, 'name' );
								if( $tmp2 == 'yea' )
								{
									if( ! $fileslist[ _DS ][ 'folders' ][ $folder ] )
									{
										$fileslist[ _DS ][ 'folders' ][ $folder ]= true;
										$fileslist[ _DS ][ 'folders' ][ 'str' ] .= '|'. $folder .'|';
									}
									if( mysql_result( $r_kws, $kk, 'type' ) == 'simple' )
										$fileslist[ $folder ][ 'files' ][ $file ][ 'id' ] .= ( ! empty( $fileslist[ $folder ][ 'files' ][ $file ][ 'id' ] ) ? ', ' : '' ) . mysql_result( $r_kws, $kk, 'id' );
											else $fileslist[ $folder ][ 'files' ][ $file ][ 'id' ]= mysql_result( $r_kws, $kk, 'id' ) .', '. $fileslist[ $folder ][ 'files' ][ $file ][ 'id' ];
												
											
									$fileslist[ $folder ][ 'files' ][ $file ][ 'type' ]= mysql_result( $r_kws, $kk, 'type' );
								}
								if( $tmp2 == 'yea' && mysql_result( $r_kws, $kk, 'type' ) == 'virus' )
								{
									if( $autodelete && ( ! $autodeletefilesize || $autodeletefilesize == $tmp_filesize ) )
									{
										unlink( $root_dir . $folder . $file );
									}else{
										if( $autocurefile ) curefile( $folder . $file );
									}
									
									$tmp= str_replace( "[[type]]", $tpl_item_type, $tpl_item );
									$tmp= str_replace( "[[directory]]", $folder, $tmp );
									$tmp= str_replace( "[[file]]", $file, $tmp );
									$tmp= str_replace( "[[fullpath]]", $folder . $file, $tmp );
									$tmp= str_replace( "[[filesize]]", $tmp_filesize, $tmp );
									$tmp= str_replace( "[[filedate]]", date( 'd-m-Y, H:i:s', filectime( $root_dir . $folder . $file ) ), $tmp );
									$tmp= str_replace( "[[signatureid]]", mysql_result( $r_kws, $kk, 'id' ), $tmp );
									$tmp= str_replace( "[[strpos]]", $kw1.'-'.$kw2.'-'.$kw3.'-'.$kw4, $tmp );
									$print_1 .= $tmp;
									
									if( ! $flag_print[ 'virus' ] )
									{
										$print_2 .= '[[VIRUS]]' .$b;
										$flag_print[ 'virus' ]= true;
									}
									if( ! $flag_print[ $tpl_item_type ] )
									{
										$print_2 .= '[[VV_'. $tpl_item_type .']]' .$b;
										$flag_print[ $tpl_item_type ]= true;
									}
									
									if( mysql_result( $r_kws, $kk, 'type' ) == 'virus' ) break;
								}
							}
							fclose( $o );
						}
					}
					
					if( ( $tm > 0 && filectime( $root_dir . $folder . $file ) > $tm ) || $simplelineflag )
					{
						$tpl_item_type= ( $simplelineflag ? '~ уведомление ~' : 'FileCTime' );
						$tmp= str_replace( "[[type]]", $tpl_item_type, $tpl_item );
						$tmp= str_replace( "[[directory]]", $folder, $tmp );
						$tmp= str_replace( "[[file]]", $file, $tmp );
						$tmp= str_replace( "[[fullpath]]", $folder . $file, $tmp );
						$tmp= str_replace( "[[filesize]]", $tmp_filesize, $tmp );
						$tmp= str_replace( "[[filedate]]", date( 'd-m-Y, H:i:s', filectime( $root_dir . $folder . $file ) ), $tmp );
						$print_1 .= $tmp;
						
						if( ! $flag_print[ 'virus' ] )
						{
							$print_2 .= '[[VIRUS]]' .$b;
							$flag_print[ 'virus' ]= true;
						}
						if( ! $flag_print[ $tpl_item_type ] )
						{
							$print_2 .= '[[VV_'. $tpl_item_type .']]' .$b;
							$flag_print[ $tpl_item_type ]= true;
						}
					}
				}
			}elseif( is_link( $root_dir . $folder . $file ) ){
				//
			}elseif( $file != "." && $file != ".." && stripos( $ignore, '/'. $file .'/' ) === false ){
				if( $level_of_directory < $maxlvl || $maxlvl == '' )
				{
					$level_of_directory += 1;
					buran( $folder . $file . _DS, $fileslist );
				}else{
					if( ! $flag_print[ 'max_level_directrory_this_directory' ] )
					{
						$tmp= str_replace( "[[directory]]", $folder, $tpl_maxlvl );
						$print_1 .= $tmp;
						$flag_print[ 'max_level_directrory_this_directory' ]= true;
					}
					if( ! $flag_print[ 'maxlvl' ] )
					{
						$print_2 .= '[[MAXLEVEL]]' .$b;
						$flag_print[ 'maxlvl' ]= true;
					}
				}
			}
		}
		$level_of_directory -= 1;
		closedir( $open );
	}else{
		$print_1 .= str_replace( "[[directory]]", $folder, $tpl_err_opendir );
		$print_2 .= '[[OPEN_ERR]]' .$b;
	}
}
function cms()
{
	//v003
	global $root_dir;
	
	@include( $root_dir ._DS.'manager/includes/version.inc.php' );
	if( ! empty( $modx_full_appname ) )
	{
		$cms= 'modx_evo';
		$cmsname= $modx_full_appname;
		$cmsver= $modx_version;
	}
	@include( $root_dir ._DS. 'configuration.php' );
	if( class_exists( 'JConfig' ) ) $conf= new JConfig();
	if( $conf->host )
	{
		$cms= 'joomla';
		$cmsname= '';
		$cmsver= '';
	}
	
	return array( $cms, $cmsname, $cmsver );
}
function get_url_bez_tochek( $adres )
{
	//2.5
	$adres= str_replace( "../", "...//", $adres );
	$adres= str_replace( "./", "", $adres );
	$adres= ltrim( $adres, "\.\./" );
	
	$pattern = '/[^\/]+\/\.\.\//';
	while( preg_match( $pattern, $adres ) )
	{
		$adres= preg_replace( $pattern, '', $adres );
		//$adres= trim( $adres, "\.\./" );
	}
	
	if( substr( $adres, 0, 1 ) != '/' ) $adres= '/'. $adres;
	
	return $adres;
}
