# VicWord 一个纯php的分词


### 分词说明
- 含有3种切分方法
    - `getWord` 长度优先切分 。最快
    - `getShortWord` 细粒度切分。比最快慢一点点
    - `getAutoWord` 自动切分 (在相邻词做了递归) 。效果最好
- 可自定义词典，自己添加词语到词库，词库支持文本格式`json`和二级制格式`igb`
二进制格式词典小，加载快
- `dict.igb`含有175662个词
- 三种分词结果对比
```php
$fc = new VicWord('igb');
$arr = $fc->getWord('北京大学生喝进口红酒，在北京大学生活区喝进口红酒');
//北京大学|生喝|进口|红酒|，|在|北京大学|生活区|喝|进口|红酒
//$arr 是一个数组 每个单元的结构[词语,词语位置,词性,这个词语是否包含在词典中] 这里只值列出了词语

$arr =  $fc->getShortWord('北京大学生喝进口红酒，在北京大学生活区喝进口红酒');
//北京|大学|生喝|进口|红酒|，|在|北京|大学|生活|区喝|进口|红酒

$arr = $fc->getAutoWord('北京大学生喝进口红酒，在北京大学生活区喝进口红酒');
//北京|大学生|喝|进口|红酒|，|在|北京大学|生活区|喝|进口|红酒

//对比
//qq的分词和百度的分词 http://nlp.qq.com/semantic.cgi#page2 http://ai.baidu.com/tech/nlp/lexical

```
### 分词速度
机器阿里云 `Intel(R) Xeon(R) Platinum 8163 CPU @ 2.50GHz`   
`getWord` 每秒140w字  
`getShortWord` 每秒138w字  
`getAutoWord` 每秒40w字  
测试文本在百度百科拷贝的一段5000字的文本

### 制作词库
- 词库支持utf-8的任意字符   
- 词典大小不影响 分词速度  

只有一个方法 VicDict->add(词语,词性 = null)
```php
//定义词典文件路径
define('_VIC_WORD_DICT_PATH_',__DIR__.'/Data/dict.igb');

require __DIR__.'/Lib/VicDict.php';

//目前可支持 igb 和 json 两种词典库格式；igb需要安装igbinary扩展，igb文件小，加载快
$dict = new VicDict('igb');

//添加词语词库 add(词语,词性) 不分语言，可以是utf-8编码的任何字符
$dict->add('中国','n');

//保存词库
$dict->save();
```

### TODO

- 添加每个词语的idf值 ，有了这个可以提取文章中关键词，自动生成简介，相识文章搜索更准确
- 词语都没有词性可加上 但是没有觉得有什么用
- 做成php的扩展，现在词库2.4M加载到php就达到100M了，没法加载几百万的大词库。做成扩展预计内存和词典文件的大小差不多。

### 缺点
词库太占内存了，主要是php数组太浪费内存了。

### demo 
[demo](http://blogs.yxsss.com/my/fc)

