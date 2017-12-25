<?php
/**
 * Created by PhpStorm.
 * User: tanszhe
 * Date: 2017/12/25
 * Time: 下午7:46
 */
//定义词典文件路径
define('_VIC_WORD_DICT_PATH_',__DIR__.'/Data/dict.igb');

require __DIR__.'/Lib/VicWord.php';

//type: 词典格式
$fc = new VicWord('igb');

//长度优先分词
$ar = $fc->getWord('北京大学生喝进口红酒，在北京大学生活区喝进口红酒');
echoWord($ar);

//细切分
$ar = $fc->getShortWord('北京大学生喝进口红酒，在北京大学生活区喝进口红酒');
echoWord($ar);

//自动 这种方法最耗时
$ar = $fc->getAutoWord('北京大学生喝进口红酒，在北京大学生活区喝进口红酒');
echoWord($ar);

function echoWord($arr){
    $arr = array_map(function($v){return $v[0];},$arr);
    echo implode("|",$arr).PHP_EOL;
}

