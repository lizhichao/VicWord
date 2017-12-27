<?php

/**
 * Created by PhpStorm.
 * User: tanszhe
 * Date: 2017/12/21
 * Time: 下午8:16
 */
namespace Lizhichao\Word;

class VicDict
{
    private $word = [];
    /**
     * 词典地址
     * @var string
     */
    private $code = 'utf-8';

    private $end = ['\\' => 1];

    private $default_end = ['\\' => 1];

    private $end_key = '\\';

    private $type = 'igb';

    public function __construct($type = 'igb')
    {
        $this->type = $type;
        if (file_exists(_VIC_WORD_DICT_PATH_)) {
            if ($type == 'igb') {
                $this->word = igbinary_unserialize(file_get_contents(_VIC_WORD_DICT_PATH_));
            } else {
                $this->word = json_decode(file_get_contents(_VIC_WORD_DICT_PATH_), true);
            }
        }
    }

    /**
     * @param string $word
     * @param null|string $x 词性
     * @return bool
     */
    public function add($word, $x = null)
    {
        $this->end = ['\\x' => $x] + $this->default_end;
        $word = $this->filter($word);
        if ($word) {
            return $this->merge($word);
        }
        return false;
    }

    private function merge($word)
    {
        $ar = $this->toArr($word);
        $br = $ar;
        $wr = &$this->word;
        foreach ($ar as $i => $v) {
            array_shift($br);
            if (!isset($wr[$v])) {
                $wr[$v] = $this->dict($br, $this->end);
                return true;
            } else {
                $wr = &$wr[$v];
            }
        }
        if (!isset($wr[$this->end_key])) {
            foreach ($this->end as $k => $v) {
                $wr[$k] = $v;
                $wr[$k] = $v;
            }
        }
        return true;
    }

    public function save()
    {
        if ($this->type == 'igb') {
            $str = igbinary_serialize($this->word);
        } else {
            $str = json_encode($this->word);
        }
        return file_put_contents(_VIC_WORD_DICT_PATH_, $str);
    }

    private function filter($word)
    {
        return str_replace(["\n", ' '], '', trim($word));
    }


    private function dict($arr, $v, $i = 0)
    {
        if (isset($arr[$i])) {
            return [$arr[$i] => $this->dict($arr, $v, $i + 1)];
        } else {
            return $v;
        }
    }

    private function toArr($str)
    {
        $l = mb_strlen($str, $this->code);
        $r = [];
        for ($i = 0; $i < $l; $i++) {
            $r[] = mb_substr($str, $i, 1, $this->code);
        }
        return $r;
    }

}