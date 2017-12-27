# VicWord

中文分词

## Installing

require library

```shell
composer require xu42/vic-word
```

## Usage

1. get word

```php

// dict file path
$dictPath = '/tmp/dict.igb';

//长度优先分词
(new VicWord($dictPath))->getWord('聚知台是一个及时沟通工具');

//细切分
(new VicWord($dictPath))->getShortWord('聚知台是一个及时沟通工具');

//自动 这种方法最耗时
(new VicWord($dictPath))->getAutoWord('聚知台是一个及时沟通工具');

```

2. add dict

```php

// dict file path
$dictPath = '/tmp/dict.igb';

// 添加词库
(new VicDict($dictPath))->add('中国', n);

```


## License

[Apache 2.0](LICENSE)
