<?php
$data = $_POST['dirs'];
$name = 'downloads' . DIRECTORY_SEPARATOR . md5(implode($data)) . '.zip';
if (file_exists($name)) {
    exit(json_encode(['count' => -1, 'status' => 0, 'name' => $name], 256));
}
$Zipper = new ZipArchive;
$Zipper->open($name, ZIPARCHIVE::CREATE);
foreach ($data as $key => $value) {
    $folders = explode(DIRECTORY_SEPARATOR, $value);
    $f_name = array_pop($folders);
    array_shift($folders);
    $dir = implode(DIRECTORY_SEPARATOR, $folders);
    $Zipper->addEmptyDir($dir);
    if ($dir and $dir != '.' and $dir != '.\\') {
        $f_name = $dir . DIRECTORY_SEPARATOR . $f_name;
    }
    $Zipper->addFile($f_name);
}
echo json_encode(['count' => $Zipper->numFiles, 'status' => $Zipper->status, 'name' => $name], 256);
$Zipper->close();
$log = 'delete.log.json';
if (file_exists($log)) {
    $log_data = json_decode(file_get_contents($log), true);
}
$log_data[] = ['name' => $name, 'datetime' => date(DATE_ATOM)];
file_put_contents($log, json_encode($log_data, 256));
