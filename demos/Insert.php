<?php

declare(strict_types = 1);

include \dirname(__DIR__) . '/vendor/autoload.php';

//目前可支持 igb 和 json 两种词典库格式；igb需要安装igbinary扩展，igb文件小，加载快
$dict = new \Kmvan\Participle\Word\Insert([
    'dictType' => 'json',
]);

//添加词语词库 add(词语,词性) 可以是除保留字符（\ ， \x  ，\i），以外的utf-8编码的任何字符
$dict->add('中国', 'n');

//保存词库
$dict->save();
