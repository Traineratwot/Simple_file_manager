<?php
$cfolder = ($cfolder) ? $cfolder : $_POST['folder'];
if (!$cfolder) {
    die(json_encode(['falid' => '$cfolder'], 256));
}
$q1 = "SELECT SUM(filesize) as `s`
FROM `structure`
WHERE `path` NOT LIKE '%_Изменения за 30 дней%' AND `type`='Файл' AND `parent` LIKE '%" . $cfolder . "%' ORDER BY `path`";
$res = $modx->query($q1);
if ($res) {
    $size = $res->fetch(PDO::FETCH_ASSOC);
} else {
    die(json_encode(['falid' => $q1], 256));
}
echo json_encode(['size' => $size['s']], 256);
