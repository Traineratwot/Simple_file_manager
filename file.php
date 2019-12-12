<?php

$extension_type = [
	'image'	=> ['mime' => 'image',	 'icon' => '<i style="color:blue;" class="fas fa-file-image"></i>'],
	'xls' 	=> ['mime' => 'xls',	 'icon' => '<i style="color:green;" class="fas fa-file-excel"></i>'],
	'doc' 	=> ['mime' => 'doc',	 'icon' => '<i style="color:lightblue;" class="fas fa-file-word"></i>'],
	'css' 	=> ['mime' => 'css',	 'icon' => '<i style="color:lightblue;" class="fab fa-css3"></i>'],
	'php' 	=> ['mime' => 'php',	 'icon' => '<i style="color:magenta;" class="fas fa-file-code"></i>'],
	'js' 	=> ['mime' => 'js',		 'icon' => '<i style="color:gold;" class="fab fa-js-square"></i>'],
	'html' 	=> ['mime' => 'html',	 'icon' => '<i style="color:orange;" class="fab fa-html5"></i>'],
	'zip' 	=> ['mime' => 'zip',	 'icon' => '<i style="color:dodgerblue;" class="fas fa-file-archive"></i>'],
];
function extension_type($mime)
{
	global $extension_type;
	foreach ($extension_type as $key => $value) {
		preg_match_all('@(.{0,})(' . $value['mime'] . ')(.{0,})@', $mime, $math);
		if ($math[2]) {
			return $value;
		}
	}
	return ['icon' => '<i class="fas fa-file"></i>'];
}

function pathinfo_utf($path)
{
	if (file_exists($path)) {
		# code...
		if (strpos($path, DIRECTORY_SEPARATOR) !== false)
			$basename = end(explode(DIRECTORY_SEPARATOR, $path));
		elseif (strpos($path, '\\') !== false)
			$basename = end(explode('\\', $path));
		else
			return false;

		if (!$basename)
			return false;

		$dirname = substr(
			$path,
			0,
			strlen($path) - strlen($basename) - 1
		);

		if (strpos($basename, '.') !== false) {
			$extension = end(explode('.', $path));
			$filename = substr(
				$basename,
				0,
				strlen($basename) - strlen($extension) - 1
			);
		} else {
			$extension = '';
			$filename = $basename;
		}
		$mime = mime_content_type($path);
		$mime .= '/' . $extension;
		$timeChange = filemtime($path);
		$type = extension_type($mime);

		$weight = filesize($path);
		return array(
			'icon' => $type['icon'],
			'dirname' => $dirname,
			'basename' => $basename,
			'extension' => $extension,
			'filename' => $filename,
			'size' => $weight,
			'mime' => $mime,
			'timeChange' => $timeChange,
		);
	}
	return false;
}

function filter($prop)
{
	$responce = 0;
	//2019-12-11T10%3A59
	$from       = DateTime::createFromFormat('Y-m-d', $_POST['from']);
	$to         = DateTime::createFromFormat('Y-m-d', $_POST['to']);
	$timeChange = $prop['timeChange'];
	$timeChange = new DateTime("@$timeChange");
	$basename 	= $prop['basename'];

	$search     = $_POST['search'];
	// var_dump($from <= $timeChange and $timeChange <= $to);


	if ($from and $to) {
		if (!($from <= $timeChange and $timeChange <= $to)) {
			$responce--;
		}
	}
	if ($search) {
		preg_match_all('@(.{0,})(' . $search . ')(.{0,})@', $basename, $math);
		if (!$math[2]) {
			$responce--;
		}
	}
	if ($_POST['type']) {
		preg_match_all('@(.{0,})(' . $_POST['type'] . ')(.{0,})@', $prop['mime'], $math);
		if (!$math[2]) {
			$responce--;
		}
	}
	if ($responce === 0) {
		return true;
	} else {
		return false;
	}
}
$Responce = [];
function recursive($dir, $i = 0)
{
	static $deep = 0;
	global $Responce;
	if (!isset($_POST['onfolder']) and $dir != '.') {
		// echo '<a href="/file.php?dir=' . dirname($dir) . '"></i>..</a><br>';
		$Responce['adir' . $i] = ['path' => dirname($dir), 'name' => '..', 'icon' => '<i class="far fa-folder"></i>', 'prop' => null];
	}
	$files = scandir($dir);
	foreach ($files as $file) {
		if ($file == '.' || $file == '..') {
			continue;
		}
		$_file = $dir . DIRECTORY_SEPARATOR . $file;
		if (is_dir($_file)) {
			if (isset($_POST['onfolder'])) {
				recursive($dir . DIRECTORY_SEPARATOR . $file, $i);
			} else {
				// echo '<input type="checkbox" onchange="checin(this)" value="' . $_file . '"><i class="fas fa-folder-open"><a href="/file.php?dir=' . $_file . '"></i>' . $file . '</a><br>';
				$Responce['dir' . $i] = ['path' => $_file, 'name' => $file, 'icon' => '<i class="fas fa-folder-open"></i>'];
			}
		} else {
			$prop = pathinfo_utf($_file);
			if (!filter($prop)) {
				continue;
			}
			// echo '<input type="checkbox" onchange="checin(this)" value="' . $_file . '">' . $prop["icon"] . '<a target="_blank" href="' . $_file . '">' . $file . '</a><br>';
			$Responce['file' . $i] = ['path' => $_file, 'name' => $file, 'icon' => $prop["icon"], 'prop' => $prop];
			// var_dump($prop);
		}
		$i++;
	}
	return $Responce;
}

$dir = ($_POST['dir']) ? (string) $_POST['dir'] : '.';

echo json_encode(recursive($dir), 256);
