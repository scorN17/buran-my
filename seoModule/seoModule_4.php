<?php
// Буран
	// if( ! isset($_GET['test'])) exit();
	
	error_reporting(0);
	
	$root = $_SERVER['DOCUMENT_ROOT'];
	$root = '/home/u333528/bunker-yug.ru/www';
	
	$raz_v        = 60*60*12;
	$errors_raz_v = 60*60*1;
	$pages_raz_v  = 60*60*24;
	$limit        = 1;
	
	$table1 = 'customer AS c';
	$table2 = 'website_satellit AS ws';
	$table3 = 'website_param AS wp';
	$table4 = 'fn_delta';
	$table5 = 'custmworks AS cw';
	$table6 = 'workers AS w';
	$table8 = 'extracts AS e';
	$table9 = 'reports AS r';
	
	$bsm   = "seomodule";
	$bsm_l = "seomodule_log";
	$bsm_p = "seomodule_page";
	$bsm_c = "seomodule_config";
	
	@include_once($root."/db.php");
	@include_once($root."/idn/idna_convert.class.php");
	
	$idn = new idna_convert();
	
//=======================================================================



	$rr = mysql_query("SELECT c.id idc, c.url, b3.info, bc.config
		FROM {$table1}
		INNER JOIN {$table8} ON e.idc=c.id
		INNER JOIN {$table9} ON r.idc=c.id
		LEFT JOIN {$bsm} AS b3 ON b3.idc=c.id
		LEFT JOIN {$bsm_c} AS bc ON bc.idc=c.id
			WHERE
			(
				(
					r.`status`<>4 AND r.`status`<>8
					AND (e.ntoday>0 OR r.`status`=9)
				)
				OR r.treatment='y'
			)
			AND (
				".time()."-b3.dt>{$raz_v} OR b3.dt IS NULL
				OR (ii>=2 AND ".time()."-b3.dt>{$errors_raz_v})
			)
				ORDER BY b3.dt, c.id LIMIT {$limit}");

	if ($rr && mysql_num_rows($rr)) {
		while ($row = mysql_fetch_assoc($rr)) {
			if ($row['config']) {
				$config = base64_decode($row['config']);
				$config = unserialize($config);
				$website = $config[1]['website'];
			}
			if ( ! $website) {
				$website = 'http://'.site_from_url(trim($row['url']));
			}
			$website_encoded = $idn->encode($website);
			$website_decoded = $idn->decode($website_encoded);

			$website_to = '';
			$status     = 'er';
			$m_status   = 'er';

			$options = array(
				CURLOPT_URL => $website_encoded.'/_buran/seoModule.php?a=info',
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_FRESH_CONNECT  => true,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_MAXREDIRS      => 10,
				CURLOPT_CONNECTTIMEOUT => 10,
				CURLOPT_TIMEOUT        => 10,
			);
			$curl = curl_init();
			curl_setopt_array($curl, $options);
			$response = curl_exec($curl);
			$request_info = curl_getinfo($curl);
			curl_close($curl);
			if ( ! curl_errno($curl) && $request_info['http_code'] == 200) {
				$status = 'ok';
				if (stripos($response, '[seomoduleversion_') !== false) {
					$m_status = 'ok';
				}
			}

			if ($status != 'ok') {
				$options = array(
					CURLOPT_URL => $website_encoded,
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_FRESH_CONNECT  => true,
					CURLOPT_FOLLOWLOCATION => true,
					CURLOPT_MAXREDIRS      => 10,
					CURLOPT_CONNECTTIMEOUT => 10,
					CURLOPT_TIMEOUT        => 10,
				);
				$curl = curl_init();
				curl_setopt_array($curl, $options);
				$response = curl_exec($curl);
				$request_info = curl_getinfo($curl);
				curl_close($curl);
				if ( ! curl_errno($curl) && $request_info['http_code'] == 200
					&& $response) {
					$status = 'ok';
				}
			}




			$time = time();



			if ($m_status == 'ok') {
				if (preg_match("/\[seomoduleversion_(.*)\]/iU", $response, $matches) === 1) {
					$version = $matches[1];
					$version = preg_replace("/0-9\./", '', $version);
					$version = floatval($version);
				}

				if (preg_match("/\[website_(.*)\]/iU", $response, $matches) === 1) {
					$website_to = $matches[1];
				}

				$o_seohash = false;
				if (preg_match("/\[seohash_(.*)\]/U", $row['info'], $matches) === 1) {
					$o_seohash = $matches[1];
				}
				$o_modulehash = false;
				if (preg_match("/\[modulehash_(.*)\]/U", $row['info'], $matches) === 1) {
					$o_modulehash = $matches[1];
				}
				$o_confighash = false;
				if (preg_match("/\[confighash_(.*)\]/U", $row['info'], $matches) === 1) {
					$o_confighash = $matches[1];
				}
				$o_stylehash = false;
				if (preg_match("/\[stylehash_(.*)\]/U", $row['info'], $matches) === 1) {
					$o_stylehash = $matches[1];
				}

				$seohash = false;
				if (preg_match("/\[seohash_(.*)\]/U", $response, $matches) === 1) {
					$seohash = $matches[1];
				}
				$modulehash = false;
				if (preg_match("/\[modulehash_(.*)\]/U", $response, $matches) === 1) {
					$modulehash = $matches[1];
				}
				$confighash = false;
				if (preg_match("/\[confighash_(.*)\]/U", $response, $matches) === 1) {
					$confighash = $matches[1];
				}
				$stylehash = false;
				if (preg_match("/\[stylehash_(.*)\]/U", $response, $matches) === 1) {
					$stylehash = $matches[1];
				}

				if ($o_modulehash || $modulehash) {
					if ($modulehash != $o_modulehash) {
						mysql_query("INSERT INTO {$bsm_l} SET
							idc = {$row['idc']},
							txt = 'Модуль изменен',
							dth = '".date('Y-m-d-H-i-s')."',
							dt  = '{$time}'");
					}
					if ($confighash != $o_confighash) {
						mysql_query("INSERT INTO {$bsm_l} SET
							idc = {$row['idc']},
							txt = 'Конфигурация изменена',
							dth = '".date('Y-m-d-H-i-s')."',
							dt  = '{$time}'");
					}
					if ($stylehash != $o_stylehash) {
						mysql_query("INSERT INTO {$bsm_l} SET
							idc = {$row['idc']},
							txt = 'Стиль изменен',
							dth = '".date('Y-m-d-H-i-s')."',
							dt  = '{$time}'");
					}

				} elseif ($o_seohash || $seohash) {
					if ($seohash != $o_seohash) {
						mysql_query("INSERT INTO {$bsm_l} SET
							idc = {$row['idc']},
							txt = 'Модуль изменен',
							dth = '".date('Y-m-d-H-i-s')."',
							dt  = '{$time}'");
					}
				}

				preg_match("/\[pages_\](.*)\[_pages\]/s", $row['info'], $matches);
				$pages = str_replace("\r", '', $matches[1]);
				$pages = explode("\n", $pages);
				$o_pages = array();
				if (is_array($pages)) {
					foreach ($pages AS $page) {
						if ($version >= 4) {
							$page = explode(' : ', $page);
							$o_pages[$page[0]] = $page;
						} else {
							$page = explode(' => ', $page);
							if ( ! $page[1]) continue;
							$o_pages[$page[0]] = $page;
						}
					}
				}

				preg_match("/\[pages_\](.*)\[_pages\]/s", $response, $matches);
				$pages = str_replace("\r", '', $matches[1]);
				$pages = explode("\n", $pages);
				if (is_array($pages)) {
					foreach ($pages AS $page) {
						$page = explode(' => ', $page);
						if ( ! $page[1]) continue;

						$url  = mysql_real_escape_string($page[0]);
						$file = explode(':', $page[1]);
						$tp   = $file[0];
						$sh   = $file[1];
						$file = end($file);
						$file = mysql_real_escape_string($file);

						$rrr = mysql_query("SELECT * FROM {$bsm_p}
							WHERE idc={$row['idc']} AND url='{$url}' LIMIT 1");

						if ($rrr && mysql_num_rows($rrr)) {
							mysql_query("UPDATE {$bsm_p} SET
								tp   = '{$tp}',
								file = '{$file}',
								sh   = '{$sh}',
								dth  = '".date('Y-m-d-H-i-s')."',
								dt   = '{$time}'
							WHERE idc={$row['idc']} AND url='{$url}' LIMIT 1");

						} elseif ($rrr) {
							mysql_query("INSERT INTO {$bsm_p} SET
								idc  = {$row['idc']},
								tp   = '{$tp}',
								url  = '{$url}',
								file = '{$file}',
								sh   = '{$sh}',
								ii   = 1,
								dth  = '".date('Y-m-d-H-i-s')."',
								dt   = '{$time}'
							");
						}

						if ($page[2] !== $o_pages[$page[0]][2]) {
							mysql_query("INSERT INTO {$bsm_l} SET
								idc  = {$row['idc']},
								file = '{$file}',
								txt  = 'Файл изменен',
								dth  = '".date('Y-m-d-H-i-s')."',
								dt   = '{$time}'");
						}
					}
				}
			}

			if ( ! $website_to) $website_to = $website_encoded;

			$website_to = mysql_real_escape_string($website_to);
			$response   = mysql_real_escape_string($response);

			$rrr = mysql_query("SELECT id, ii, status, m_status FROM {$bsm}
				WHERE idc='{$row[idc]}' LIMIT 1");
			if ($rrr && mysql_num_rows($rrr)) {
				$my = mysql_fetch_assoc($rrr);

				$qq = '';
				if ($status != $my['status'] || $m_status != $my['m_status']) {
					if ($my['ii'] >= 3) {
						$qq .= "ii=1,";
						$qq .= "status='{$status}',";
						$qq .= "m_status='{$m_status}',";
					} else {
						$qq .= "ii=ii+1,";
					}
				} else {
					$qq .= "ii=1,";
				}

				mysql_query("UPDATE {$bsm} SET
					".($m_status == 'ok'
						? "website_to='{$website_to}', info='{$response}'," : "")."
					{$qq}
					website = '{$website_decoded}',
					dth     = '".date('Y-m-d-H-i-s')."',
					dt      = '{$time}'
					WHERE idc='{$row['idc']}' LIMIT 1");
				
			} elseif ($rrr) {
				mysql_query("INSERT INTO {$bsm} SET
					".($m_status == 'ok'
						? "website_to='{$website_to}', info='{$response}'," : "")."
					idc      = '{$row[idc]}',
					website  = '{$website_decoded}',
					ii       = '1',
					status   = '{$status}',
					m_status = '{$m_status}',
					dth      = '".date('Y-m-d-H-i-s')."',
					dt       = '{$time}'
				");
			}
		}
	}

	$rr = mysql_query("SELECT c.id AS idc, c.url, b3.website, b3.validation_cc
		FROM {$table1}
		INNER JOIN {$table8} ON e.idc=c.id
		INNER JOIN {$table9} ON r.idc=c.id
		LEFT JOIN {$bsm} AS b3 ON b3.idc=c.id
			WHERE r.`status`<>4 AND r.`status`<>8 AND e.ntoday>0
			AND b3.m_status='ok' AND ".time()."-b3.dt_validation>{$raz_v}
				ORDER BY b3.dt_validation, c.id LIMIT {$limit}");

	if ($rr && mysql_num_rows($rr)) {
		while ($row = mysql_fetch_assoc($rr)) {
			$flag = true;
			$rr2 = mysql_query("SELECT * FROM cust_dostup WHERE
				idc={$row[idc]} AND (type='ftp' OR type='sftp' OR type='ssh')
					AND deleted=0");
			if ($rr2 && mysql_num_rows($rr2)) {
				$flag = false;
				while ($row2 = mysql_fetch_assoc($rr2)) {
					if ($row2['check']>0 && $row2['flag'] == 1) {
						$flag = true;
						break;
					}
				}
			}
			
			mysql_query("UPDATE {$bsm} SET
				validation_cc  = ".($flag ? "0" : "validation_cc+1").",
				dth_validation = '".date('Y-m-d-H-i-s')."',
				dt_validation  = ".time()."
				WHERE idc='{$row[idc]}' LIMIT 1");

			if ( ! $flag && $row['validation_cc'] >= 2 && $row['validation_cc'] <= 10) {
				if ($row['website']) $website = $row['website'];
				else $website = 'http://'.site_from_url(trim($row['url']));
				$website_encoded = $idn->encode($website);
				$website_decoded = $idn->decode($website_encoded);
				
				$host = site_from_url($row['website_to']);
				$w = @file_get_contents('http://bunker-yug.ru/__buran/secret_key.php?s=V_s68g_Kw79eRYL6EsST&h='.$host);

				$curl = curl_init();
				curl_setopt($curl, CURLOPT_URL, $website_encoded.'/_buran/seoModule.php?a=validation');
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
				$response = curl_exec($curl);
				curl_close($curl);

				$curl = curl_init();
				curl_setopt($curl, CURLOPT_URL, $website_encoded.'/_buran/seoModule.php?a=deactivate&w='.$w);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
				$response = curl_exec($curl);
				curl_close($curl);
			}
		}
	}












	$rr = mysql_query("SELECT
		mm.idc, mm.website_to, pp.id, pp.url, pp.file
		FROM {$bsm_p} pp
		INNER JOIN {$bsm} mm ON mm.idc=pp.idc
		WHERE ".time()."-pp.dt_check>={$pages_raz_v} AND mm.m_status='ok' AND mm.ii=1
		ORDER BY pp.dt LIMIT 5");
	if ($rr && mysql_num_rows($rr)) {
		while ($row = mysql_fetch_assoc($rr)) {
			$options = array(
				CURLOPT_URL => $row['website_to'] . $row['url'],
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_FRESH_CONNECT  => true,
				CURLOPT_FOLLOWLOCATION => false,
				CURLOPT_CONNECTTIMEOUT => 10,
				CURLOPT_TIMEOUT        => 10,
			);
			$curl = curl_init();
			curl_setopt_array($curl, $options);
			$response = curl_exec($curl);
			$request_info = curl_getinfo($curl);
			curl_close($curl);
			if ( ! curl_errno($curl) && $response
				&& $request_info['http_code'] == 200) {
				$status = 'ok';
				$pagecode = $response;
			} else {
				$status = 'er';
				$pagecode = false;
			}

			$filecode = false;
			if ($pagecode) {
				$options = array(
					CURLOPT_URL => $row['website_to'] .'/_buran/seoModule.php?a=file&f='.$row['file'],
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_FRESH_CONNECT  => true,
					CURLOPT_FOLLOWLOCATION => false,
					CURLOPT_CONNECTTIMEOUT => 10,
					CURLOPT_TIMEOUT        => 10,
				);
				$curl = curl_init();
				curl_setopt_array($curl, $options);
				$response = curl_exec($curl);
				$request_info = curl_getinfo($curl);
				curl_close($curl);
				if ( ! curl_errno($curl) && $response
					&& strpos($response, '<?php') !== false) {
					$filecode = $response;
				} else {
					$filecode = false;
				}
			}

			$text_exists = false;
			if ($filecode !== false) {
				$pagecode_c = clear_text($pagecode);
				$filecode_c = clear_text($filecode);

				if (is_array($pagecode_c) && is_array($filecode_c)) {
					$text_cc = 0;
					$text_ok = 0;
					foreach ($filecode_c AS $line) {
						$text_cc++;
						if (in_array($pagecode_c, $line) !== false) {
							$text_ok++;
						}
					}

					if ($text_cc) {
						$text_exists = $text_ok / $text_cc;
					} else {
						$text_exists = 1;
					}
				}

				$filecode_f = base64_encode($filecode);
				$filecode_f = mysql_real_escape_string($filecode_f);

				$res = mysql_query("SELECT id, dt FROM seomodule_text
					WHERE idc={$row['idc']} AND alias='{$row['file']}' LIMIT 1");
				$seotext = false;
				if ($res) {
					$seotext = true;
					if (mysql_num_rows($res)) {
						$seotext = false;
					}
				}
				if ($seotext) {
					unset($title);
					unset($description);
					unset($keywords);
					unset($s_title);
					unset($s_text);
					unset($pic1);
					unset($pic2);
					unset($pic3);
					unset($pic4);
					unset($pic5);
					unset($pic6);
					unset($pic7);
					unset($pic8);
					unset($pic9);
					$evl = str_replace('<?php', '', $filecode);
					eval($evl);
					if ($title && $description && $keywords && $s_title && $s_text) {
						if (is_array($s_text)) {
							$newtext = '';
							foreach ($s_text AS $txt) {
								if ($newtext) $newtext .= '[part]'."\n";
								$newtext .= $txt."\n";
							}
							$s_text = $newtext;
						}
						$s_text = str_replace("\t", '', $s_text);
						$s_text = str_replace("\n\n", "\n", $s_text);

						$s_img = array();
						for ($pic=1; $pic<=99; $pic++) {
							if ( ! ${'pic'.$pic}) break;
							$s_img[] = ${'pic'.$pic};
						}
						$s_img = serialize($s_img);
						$s_img = base64_encode($s_img);

						$title       = mysql_real_escape_string($title);
						$description = mysql_real_escape_string($description);
						$keywords    = mysql_real_escape_string($keywords);
						$s_title     = mysql_real_escape_string($s_title);
						$s_text      = mysql_real_escape_string($s_text);
						$s_img       = mysql_real_escape_string($s_img);

						mysql_query("INSERT INTO seomodule_text SET
							idc         = '{$row['idc']}',
							alias       = '{$row['file']}',
							title       = '{$title}',
							description = '{$description}',
							keywords    = '{$keywords}',
							s_title     = '{$s_title}',
							s_text      = '{$s_text}',
							s_img       = '{$s_img}',
							dt          = ".time());
					}
				}
			}
			
			mysql_query("UPDATE {$bsm_p} SET
				".($status == 'ok' ? "ii=1," : "ii=ii+1,")."
				".($filecode
					? "filecode='{$filecode_f}',
						text_exists ='{$text_exists}',
						dth_text   = '".date('Y-m-d-H-i-s')."',
						dt_text    = '".time()."',"
					: '')."
				status    = '{$status}',
				dth_check = '".date('Y-m-d-H-i-s')."',
				dt_check  = '".time()."'
				WHERE id={$row['id']} LIMIT 1");
		}
	}









	$res = mysql_query("SELECT sc.*, sm.website_to FROM seomodule_config sc
		INNER JOIN seomodule sm ON sm.idc=sc.idc
		WHERE sm.m_status='ok' AND sm.ii=1 AND info LIKE '[seomoduleversion_4%'
		AND (sc.dt_config>sc.upd_config OR sc.dt_style>sc.upd_style OR
			sc.dt_head>sc.upd_head OR sc.dt_body>sc.upd_body)
				LIMIT 1");
	if ($res && mysql_num_rows($res)) {
		while ($row = mysql_fetch_assoc($res)) {
			$host = site_from_url($row['website_to']);
			$w = @file_get_contents('http://bunker-yug.ru/__buran/secret_key.php?s=V_s68g_Kw79eRYL6EsST&h='.$host);
			
			if ($row['dt_config'] > $row['upd_config']) {
				$type = 'config';
				$data = $row[$type];

			} elseif ($row['dt_style'] > $row['upd_style']) {
				$type = 'style';
				$data = $row[$type];
				$data = base64_decode($data);
				$data = css_compress($data);
				$data = base64_encode($data);

			} elseif ($row['dt_head'] > $row['upd_head']) {
				$type = 'head';
				$data = $row[$type];

			} elseif ($row['dt_body'] > $row['upd_body']) {
				$type = 'body';
				$data = $row[$type];

			} else {
				continue;
			}
			
			$options = array(
				CURLOPT_URL => $row['website_to'] .'/_buran/seoModule.php?a=update&w='.$w.'&t='.$type,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_FRESH_CONNECT  => true,
				CURLOPT_FOLLOWLOCATION => false,
				CURLOPT_CONNECTTIMEOUT => 10,
				CURLOPT_TIMEOUT        => 10,
				CURLOPT_POST           => true,
				CURLOPT_POSTFIELDS     => array('c'=>$data),
			);
			$curl = curl_init();
			curl_setopt_array($curl, $options);
			$response = curl_exec($curl);
			curl_close($curl);
			if ($response == 'ok') {
				$upd = true;
			} else {
				$upd = false;
			}
			mysql_query("UPDATE seomodule_config SET
				".($upd ? "upd_{$type}=".time() : "upd_{$type}=upd_{$type}+1")."
				WHERE id={$row['id']} LIMIT 1");
		}
	}





	$res = mysql_query("SELECT sc.*, sm.website_to FROM seomodule_text sc
		INNER JOIN seomodule sm ON sm.idc=sc.idc
		WHERE sm.m_status='ok' AND sm.ii=1 AND info LIKE '[seomoduleversion_4%'
			AND sc.dt>sc.upd OR sc.dt_imgs>sc.upd_imgs LIMIT 1");
	if ($res && mysql_num_rows($res)) {
		while ($row = mysql_fetch_assoc($res)) {
			$host = site_from_url($row['website_to']);
			$w = @file_get_contents('http://bunker-yug.ru/__buran/secret_key.php?s=V_s68g_Kw79eRYL6EsST&h='.$host);
			
			if ($row['dt'] > $row['upd']) {
				$row['s_img']  = base64_decode($row['s_img']);
				$row['s_img']  = unserialize($row['s_img']);

				$s_text = $row['s_text'];
				$s_text = str_replace('<p>[img]</p>', '[img]', $s_text);
				$s_text = str_replace('<p>[col]</p>', '[col]', $s_text);
				$s_text = str_replace('<p>[part]</p>', '[part]', $s_text);
				preg_match_all("/\[tab(.*)\]/U", $s_text, $matches);
				if (is_array($matches[0])) {
					foreach ($matches[0] AS $mt) {
						$s_text = str_replace('<p>'.$mt.'</p>', $mt, $s_text);
					}
				}
				$row['s_text'] = $s_text;

				$data = array(
					'title'       => $row['title'],
					'description' => $row['description'],
					'keywords'    => $row['keywords'],
					's_title'     => $row['s_title'],
					's_text'      => $row['s_text'],
					's_img'       => $row['s_img'],
				);

				$data = serialize($data);
				$data = base64_encode($data);
				
				$options = array(
					CURLOPT_URL => $row['website_to'] .'/_buran/seoModule.php?a=update&w='.$w.'&t=text&n='.$row['alias'],
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_FRESH_CONNECT  => true,
					CURLOPT_FOLLOWLOCATION => false,
					CURLOPT_CONNECTTIMEOUT => 10,
					CURLOPT_TIMEOUT        => 10,
					CURLOPT_POST           => true,
					CURLOPT_POSTFIELDS     => array('c'=>$data),
				);
				$curl = curl_init();
				curl_setopt_array($curl, $options);
				$response = curl_exec($curl);
				curl_close($curl);
				$upd = false;
				if ($response == 'ok') {
					$upd = true;
				}
				mysql_query("UPDATE seomodule_text SET
					".($upd ? "upd=".time() : "upd=upd+1")."
					WHERE id={$row['id']} LIMIT 1");
			}

			// --------------------------------------------------

			if ($row['dt_imgs'] > $row['upd_imgs']) {
				$fold = '/__buran/seomodule/images/'.$row['idc'].'/'.$row['alias'].'_';
				$imgs = glob($root.$fold.'[0-9]*.{jpg,png}', GLOB_BRACE);
				$data = array('fs' => 0);
				if (is_array($imgs)) {
					foreach ($imgs AS $key => $path) {
						ImgCrop8($path, 300,200);
						$data['fs'] ++;
						$data['f'.($key+1)] = '@'.$path;
					}
				}
				$upd = true;
				if ($data['fs']) {
					$upd = false;
					$options = array(
						CURLOPT_URL => $row['website_to'] .'/_buran/seoModule.php?a=update&w='.$w.'&t=imgs&n='.$row['alias'],
						CURLOPT_RETURNTRANSFER => true,
						CURLOPT_FRESH_CONNECT  => true,
						CURLOPT_FOLLOWLOCATION => false,
						CURLOPT_CONNECTTIMEOUT => 10,
						CURLOPT_TIMEOUT        => 10,
						CURLOPT_POST           => true,
						CURLOPT_POSTFIELDS     => $data,
						CURLOPT_SAFE_UPLOAD    => false,
					);
					$curl = curl_init();
					curl_setopt_array($curl, $options);
					$response = curl_exec($curl);
					curl_close($curl);
					if ($response == 'ok') {
						$upd = true;
					}

					foreach ($imgs AS $key => $path) {
						unlink($path);
					}
				}
				mysql_query("UPDATE seomodule_text SET
					".($upd ? "upd_imgs=".time() : "upd_imgs=upd_imgs+1")."
					WHERE id={$row['id']} LIMIT 1");
			}
		}
	}








//=======================================================================


function css_compress($data)
{
	$pregreplace = array(
		"/\/\*(.*)\*\//sU"          => "",
		"/[\s]{2,}/"                => " ",
		"/[\s]*([{\}\[\];:])[\s]*/" => '${1}',
		"/[\s]*([,>])[\s]*/"        => '${1}',
		"/([^0-9])0px/"             => '${1}0',
		"/;\}/"                     => '}',
		"/\)and\(/"                 => ') and (',
	);
	foreach ($pregreplace AS $pattern => $replacement) {
		$data = preg_replace($pattern, $replacement, $data);
	}
	return $data;
}


function clear_text($text)
{
	$trans = array(
		'А'=>'а', 'Б'=>'б', 'В'=>'в', 'Г'=>'г', 'Д'=>'д', 'Е'=>'е',
		'Ё'=>'ё', 'Ж'=>'ж', 'З'=>'з', 'И'=>'и', 'Й'=>'й', 'К'=>'к',
		'Л'=>'л', 'М'=>'м', 'Н'=>'н', 'О'=>'о', 'П'=>'п', 'Р'=>'р',
		'С'=>'с', 'Т'=>'т', 'У'=>'у', 'Ф'=>'ф', 'Х'=>'х', 'Ц'=>'ц',
		'Ч'=>'ч', 'Ш'=>'ш', 'Щ'=>'щ', 'Э'=>'э', 'Ю'=>'ю', 'Я'=>'я',
		'Ы'=>'ы', 'Ъ'=>'ъ', 'Ь'=>'ь',
	);
	$text = strtr($text, $trans);
	$text = htmlentities($text);
	$text = str_replace("\r", '', $text);
	$text = preg_replace("/[^абвгдеёжзийклмнопрстуфхцчшщьъыэюя\n]/", '-', $text);
	do {
		$text = str_replace('--', '-', $text, $count);
	} while ($count);
	# $text = preg_replace("/([-]){2,}/", '\1', $text);
	$text = explode("\n", $text);
	foreach ($text AS $key => &$row) {
		$row = trim($row, '-');
		if ( ! $row || strlen($row) <= 70) {
			unset($text[$key]);
		}
	}
	return $text;
}



function curl( $url )
{
	$head= array(
		'User-Agent: Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.135 Safari/537.36'
	);
	
	$curl= curl_init();
	
	$options= array(
				CURLOPT_URL => $url,
				CURLOPT_HTTPHEADER => $head,
				CURLOPT_AUTOREFERER => true,
				CURLOPT_COOKIESESSION => true,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_FRESH_CONNECT => true,
				//CURLINFO_HEADER_OUT => true,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_NOBODY => true,
				CURLOPT_TIMEOUT => 20
	);
	curl_setopt_array( $curl, $options );
	$result= curl_exec( $curl );
	$info= curl_getinfo( $curl );
	curl_close( $curl );
	
	return array( 'body' => $result, 'info' => $info );
}

function page_from_url( $str )
{
	$str= trim( $str );
	$str= str_replace( "https://", '', $str );
	$str= str_replace( "http://", '', $str );
	$str= str_replace( "../", '.../', $str );
	$str= str_replace( "./", '/', $str );
	$str= str_replace( "//", '/', $str );
	
	$str2= explode( "/", $str );
	$str= str_replace( $str2[0], '', $str );
	$str= trim( $str );
	
	return $str;
}


	//print get_full_url( 'javascript:post(sdf.ru', 'domain.ru', 'http://domain.ru/?bbb=222' );


function get_full_url( $url, $webs, $page )
{
	$tmp= trim( $url );
	
	$page= explode( "?", $page );
	$page= $page[ 0 ];
	
	$arr= explode( "/", page_from_url( $page ) );
	unset( $arr[ count( $arr ) - 1 ] );
	unset( $arr[ 0 ] );
	$dirname= '';
	foreach( $arr AS $val )
	{
		$dirname .= "/". $val;
	}
	$dirname .= "/";
	
	if( substr( $tmp, 0, 7 ) == 'mailto:' ) $tmp= 'http://' . $webs . '/';
	elseif( substr( $tmp, 0, 11 ) == 'javascript:' ) $tmp= 'http://' . $webs . '/';
	elseif( substr( $tmp, 0, 2 ) == '//' ) $tmp= 'http:' . $tmp;
	elseif( substr( $tmp, 0, 1 ) == '/' ) $tmp= 'http://' . $webs . get_url_bez_tochek( $tmp );
	elseif( substr( $tmp, 0, 7 ) == 'http://' ) NULL;
	elseif( substr( $tmp, 0, 8 ) == 'https://' ) NULL;
	elseif( substr( $tmp, 0, 1 ) == '?' ) $tmp= $page . $url;
	else $tmp= 'http://'. $webs . get_url_bez_tochek( ( $dirname != 'http:/' ? $dirname : '' ) . $tmp );
	
	$tmp= explode( "#", $tmp );
	$tmp= $tmp[ 0 ];
	
	$tmp= str_replace( "www.", '', $tmp );
	
	return $tmp;
}


function get_url_bez_tochek( $adres )
{
	$adres= str_replace( "../", "...//", $adres );
	$adres= str_replace( "./", "", $adres );
	$adres= ltrim( $adres, "\.\./" );
	
	$pattern = '/\w+\/\.\.\//';
	while( preg_match( $pattern, $adres ) )
	{
		$adres= preg_replace( $pattern, '', $adres );
		$adres= trim( $adres, "\.\./" );
	}
	
	if( substr( $adres, 0, 1 ) != '/' ) $adres= '/'. $adres;
	
	return $adres;
}



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



function ImgCrop8($img, $w, $h, $droot='')
{
	/**
	 * ImgCrop8
	 *
	 * @version   8.0-m
	 * @date      16.06.2017
	 */

	$img = trim(urldecode($img));
	$w = intval($w);
	$h = intval($h);

	$img = (strpos($img,'/') !==0 ? '/' : '').$img;

	$img1_info = getimagesize($droot.$img);
	$srcW = $img1_info[0];
	$srcH = $img1_info[1];
	if ( ! $srcH ) return false;
	$ot = $srcW / $srcH;
	$dstW = $w > 0 ? $w : $srcW;
	$dstH = $h > 0 ? $h : $srcH;
	if (($srcW > $w && $w > 0) || ($srcH > $h && $h > 0)) {
		$dstH = round($dstW / $ot);
		if($dstH > $h && $h > 0) {
			$dstH = $h;
			$dstW = round($dstH * $ot);
		}
	} else {
		$dstW = $srcW;
		$dstH = $srcH;
	}
	$crW = $dstW;
	$crH = $dstH;

	if ($img1_info[2] == 2) $img1 = imagecreatefromjpeg($droot.$img);
	elseif ($img1_info[2] == 3) {
		$img1 = imagecreatefrompng($droot.$img);
		$png = true;
	}
	$img2 = ImageCreateTrueColor($crW, $crH);
	if ($png) {
		imagealphablending($img2, true);
		imagesavealpha($img2, true);
		$col = imagecolorallocatealpha($img2, 255,255,255,127);
	} else {
		$col = imagecolorallocate($img2, 255,255,255);
	}
	imagefill($img2, 0,0, $col);
	imagecopyresampled($img2, $img1, 0,0,0,0, $dstW, $dstH, $srcW, $srcH);

	if ($png) imagepng($img2, $droot.$img);
	elseif ($img1_info[2] == 2) imagejpeg($img2, $droot.$img, 80);
	imagedestroy($img1);
	imagedestroy($img2);
}
