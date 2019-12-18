<?php

$extkns = [
    'images'    => ['mime' => ['jpg', 'png'],   'icon' => '<i style="color:blue;" class="fas fa-file-image"></i>'],
    'jpg'    => ['mime' => ['jpg'],              'icon' => '<i style="color:blue;" class="fas fa-file-image"></i>'],
    'png'    => ['mime' => ['png'],              'icon' => '<i style="color:blue;" class="fas fa-file-image"></i>'],
    'xls'     => ['mime' => ['xls', 'xlsx'],        'icon' => '<i style="color:green;" class="fas fa-file-excel"></i>'],
    'doc'     => ['mime' => ['doc', 'dox'],        'icon' => '<i style="color:lightblue;" class="fas fa-file-word"></i>'],
    'pdf'     => ['mime' => ['pdf'],                'icon' => '<i style="color:#CC0000;" class="fas fa-file-pdf"></i>'],
    'pptx'     => ['mime' => ['pptx'],                'icon' => '<i style="color:orange;" class="fas fa-file-powerpoint"></i>'],
    //'css' 	=> ['mime' => ['css',],         'icon' => '<i style="color:lightblue;" class="fab fa-css3"></i>'],
    //'php' 	=> ['mime' => ['php',],         'icon' => '<i style="color:magenta;" class="fas fa-file-code"></i>'],
    //'js' 	=> ['mime' => ['js',],              'icon' => '<i style="color:gold;" class="fab fa-js-square"></i>'],
    //'html' 	=> ['mime' => ['html',],        'icon' => '<i style="color:orange;" class="fab fa-html5"></i>'],
    'zip'     => ['mime' => ['zip', 'rar',],      'icon' => '<i style="color:dodgerblue;" class="fas fa-file-archive"></i>'],
    'videos' => ['mime' => ['mp4', 'avi',],        'icon' => '<i style="color:red;" class="fas fa-file-video"></i>'],
];
define('page_size', 99);
?>
<? if ($html) : ?>
    <option value="" selected>любой</option>
    <? foreach ($extkns as $key => $item) : ?>
        <option value="<?= $key ?>"><?= $key ?><?= $item['icon'] ?></option>
    <? endforeach; ?>
<? endif;
