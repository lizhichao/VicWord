<?php

declare(strict_types = 1);

include \dirname(__DIR__) . '/vendor/autoload.php';

//type: 词典格式
$word = new \Kmvan\Participle\Word\Query([
    'dictType' => 'igb',
]);

//长度优先分词
$ar = $word->getWord('聚知台是一个及时沟通工具');

//细切分
$ar = $word->getShortWord('聚知台是一个及时沟通工具');

//自动 这种方法最耗时
$ar = $word->getAutoWord('聚知台是一个及时沟通工具');
\print_r($ar);

/*
Array
(
    [0] => Array
        (
            [0] => 聚知台 // 词语
            [1] => 8     // 词语的位置 utf-8编码
            [2] =>       // 词性 tip:词库里面没有词性 欢迎大家添加
            [3] => 1     // 1 词典含有该词语 0没有该词语
        )

    [1] => Array
        (
            [0] => 是
            [1] => 10
            [2] =>
            [3] => 1
        )

    [2] => Array
        (
            [0] => 一个
            [1] => 16
            [2] =>
            [3] => 1
        )

    [3] => Array
        (
            [0] => 及时
            [1] => 23
            [2] =>
            [3] => 1
        )

    [4] => Array
        (
            [0] => 沟通
            [1] => 29
            [2] =>
            [3] => 1
        )

    [5] => Array
        (
            [0] => 工具
            [1] => 36
            [2] =>
            [3] => 1
        )

)

 */
