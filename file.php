<?php

// ->fetch(PDO::FETCH_ASSOC)
// set_time_limit(120);
// ini_set('display_errors', 0);
// ini_set('display_startup_errors', 0);
// $modx = new mysqli('localhost', 'root', '', 'modx');
$html = false;
require('assets/changes/changes_settings.php');
function extension_type($mime, $extension_type)
{
	foreach ($extension_type as $value) {
		$ext = strtolower(trim($mime));
		if (in_array($ext, $value['mime'])) {
			return $value;
		}
	}
	return ['icon' => '<i class="fas fa-file"></i>'];
}

$Responce = [];

function recursive($dir, $extension_type, $i = 0)
{
	global $Responce, $modx;
	if (!isset($_POST['onfolder']) and $dir != '.') {
		$count = $modx->query("SELECT count(*) as `x` from `structure` WHERE  `parent` LIKE '%" . $dir . "'");
		if ($count) {
			$count = $count->fetch(PDO::FETCH_ASSOC);
		} else {
			return ['msg' => '$count пипец', 'error' => $files->error, 'q' => $q1];
		}
		$page = ($_POST['page']) ? (int) $_POST['page'] : 0;
		if ($count['x'] > page_size) {
			$pages = round($count['x'] / page_size, 0, PHP_ROUND_HALF_UP);
			if ($page > $pages or $page < 0) {
				$page = 0;
			}
			$Responce['pager'] = ['page' => $page, 'page_size' => page_size, 'pages' => $pages];
		}
		$q1 = "SELECT * from `structure` WHERE `parent` LIKE '%" . $dir . "' ORDER BY `path` limit " . $page * page_size . ", " . page_size;
		$files = $modx->query($q1);
		if (!$files) {
			return ['msg' => 'files пипец', 'error' => $files->error, 'q' => $q1];
		}
		$q2 = "SELECT `parent` from `structure` WHERE `path` LIKE '%" . $dir . "' LIMIT 1";
		$root = $modx->query($q2);
		if (!$root) {
			return ['msg' => 'root пипец', 'error' => $files->error, 'q' => $q2];
		}
		$root = $root->fetch(PDO::FETCH_ASSOC);
		if ($root['parent']) {
			$Responce['adir' . 0] = ['path' => $root['parent'], 'name' => '..', 'icon' => '<i class="far fa-folder"></i>', 'prop' => null];
		}
	} else {
		$where = "WHERE `path` not like '%_Изменения за 30 дней%' and `type`='Файл'";
		if ($_POST['from'] or $_POST['to']) {
			$from       = DateTime::createFromFormat('Y-m-d', $_POST['from']);
			$to         = DateTime::createFromFormat('Y-m-d', $_POST['to']);
			$where .= ' AND `lastmod` BETWEEN ' . $from->getTimestamp() . ' AND ' . $to->getTimestamp() . '';
		}
		if ($_POST['search']) {
			$search = $_POST['search'];
			$where .= ' AND `path` LIKE "%' . $search . '%"';
		}
		if ($_POST['folder']) {
			$folder = $_POST['folder'];
			$where .= ' AND `parent` LIKE "%' . $folder . '%"';
		}
		if ($_POST['type']) {
			$type = '';
			if ($extension_type[$_POST['type']]['mime']) {
				foreach ($extension_type[$_POST['type']]['mime'] as $key => $value) {
					if ($key == 0) {
						$type .= '"' . $value . '"';
					} else {
						$type .= ',"' . $value . '"';
					}
				}
			}
			if ($type) {
				$where .= ' AND lower(`ext`) in (' . $type . ')';
			} else {
				return ['msg' => '$type пипец', 'test' => [
					$extension_type["image"], $type, $_POST['type'], $extension_type
				]];
			}
		}
		$count = $modx->query("SELECT count(*) as `x` from `structure` " . $where);
		if ($count) {
			$count = $count->fetch(PDO::FETCH_ASSOC);
		} else {
			return ['msg' => '$count пипец', 'error' => $files->error, 'q' => "SELECT count(*) as `x` from `structure` " . $where];
		}
		$page = ($_POST['page']) ? (int) $_POST['page'] : 0;
		if ($count['x'] > page_size) {
			$pages = round($count['x'] / page_size, 0, PHP_ROUND_HALF_UP);
			if ($page > $pages or $page < 0) {
				$page = 0;
			}
			$Responce['pager'] = ['page' => $page, 'page_size' => page_size, 'pages' => (int) $pages];
		}
		$q1 = "SELECT * from `structure` " . $where . " ORDER BY `path` limit " . $page * page_size . ", " . page_size;
		$files = $modx->query($q1);
		if (!$files) {
			return ['msg' => 'files пипец', 'error' => $files->error, 'q' => $q1];
		}
		$Responce['adir' . 0] = ['path' => $dir, 'name' => '..', 'icon' => '<i class="far fa-folder"></i>', 'prop' => null, 'q' => $q1];
	}
	while ($file = $files->fetch(PDO::FETCH_ASSOC)) {
		if ($file['name'] == '_Изменения за 30 дней') {
			continue;
		}
		if ($file['type'] == 'Папка') {
			if ($file['filesize'] == 0) {
				$s = json_decode($modx->runSnippet("Get_folder_size", ['cfolder' => $file['name']]), true);
				if ($s['size']) {
					$size = $s['size'];
				} else {
					$size = 0;
				}
			}
			$prop = [
				'dirname'         =>     $file['parent'],
				'basename'        =>     $file['name'],
				'filename'        =>     $file['name'],
				'timeChange'     =>    $file['lastmod'],
				'size'          =>    $size,
				'type'          =>  'folder'
			];
			$Responce['dir' . $i] = ['path' => $file['path'], 'name' => $file['name'], 'icon' => '<i class="fas fa-folder-open"></i>', 'prop' => $prop];
		} elseif ($file['type'] == 'Файл') {
			$icon = extension_type($file['ext'], $extension_type);
			$prop = [
				'icon'             =>    $icon['icon'],
				'dirname'         =>     $file['parent'],
				'basename'        =>     $file['name'],
				'extension'     =>     $file['ext'],
				'filename'        =>     $file['name'] . '.' . $file['ext'],
				'size'             =>    $file['filesize'],
				'timeChange'     =>    $file['lastmod'],
				'type'          =>  'file'
			];
			$Responce['file' . $i] = ['path' => $file['path'], 'name' => $file['name'], 'icon' => $prop["icon"], 'prop' => $prop];
		}
		$i++;
	}
	return $Responce;
}

$dir = ($_POST['dir']) ? (string) $_POST['dir'] : '/var/www/www-root/data/ftp/ftp';

echo json_encode(recursive($dir, $extkns), 256);
