<?php
set_time_limit(600);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);
$logs = [];
$data = $_POST['dirs'];
$name = '/var/www/www-root/data/ftp/assets/changes/download' . DIRECTORY_SEPARATOR . md5(implode($data)) . '.zip';
if (file_exists($name)) {
    exit(json_encode(['count' => -1, 'status' => 0, 'name' => $name], 256));
}
$Zipper = new ZipArchive;
$Zipper->open($name, ZIPARCHIVE::CREATE);
foreach ($data as $key => $source) {
    $source = str_replace('\\', DIRECTORY_SEPARATOR, $source);
    $source = str_replace('/', DIRECTORY_SEPARATOR, $source);
    if (!file_exists($source)) {
        $logs['faild'][$source] = ['code' => 1, 'msg' => 'file not found'];
        continue;
    }
    if (is_dir($source) === true) {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($files as $file) {
            $file = str_replace('\\', DIRECTORY_SEPARATOR, $file);
            $file = str_replace('/', DIRECTORY_SEPARATOR, $file);

            if ($file == '.' || $file == '..' || empty($file) || $file == DIRECTORY_SEPARATOR) {
                continue;
            }
            // Ignore "." and ".." folders
            if (in_array(substr($file, strrpos($file, DIRECTORY_SEPARATOR) + 1), array('.', '..'))) {
                continue;
            }

            $file = realpath($file);
            $file = str_replace('\\', DIRECTORY_SEPARATOR, $file);
            $file = str_replace('/', DIRECTORY_SEPARATOR, $file);

            if (is_dir($file) === true) {
                $d = str_replace($source . DIRECTORY_SEPARATOR, '', $file);
                if (empty($d)) {
                    continue;
                }
                $Zipper->addEmptyDir($d);
                $logs['ok'][$d] = ['code' => 0, 'msg' => 'добавленна папка'];
            } elseif (is_file($file) === true) {
                $Zipper->addFromString(
                    str_replace($source . DIRECTORY_SEPARATOR, '', $file),
                    file_get_contents($file)
                );
                $logs['ok'][$file] = ['code' => 0, 'msg' => 'добавленн файл'];
            } else {
                // do nothing
            }
        }
    } elseif (is_file($source) === true) {
        $Zipper->addFromString(basename($source), file_get_contents($source));
        $logs['ok'][$d] = ['code' => 0, 'msg' => 'добавленн файл'];
    }
}
$status = $Zipper->status;

echo json_encode(['count' => $Zipper->numFiles, 'status' => $status, 'name' => $name, 'log' => $logs], 256);
$Zipper->close();

$log = '/var/www/www-root/data/ftp/assets/changes/download/delete.log.json';
if (file_exists($log)) {
    $log_data = json_decode(file_get_contents($log), true);
}
$log_data[] = ['name' => $name, 'datetime' => date(DATE_ATOM)];
file_put_contents($log, json_encode($log_data, 256));
