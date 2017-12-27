<?php

namespace Xu42\VicWord;

class VicDict
{
    private $dict = [];

    private $end = ['\\' => 1];

    private $default_end = ['\\' => 1];

    private $end_key = '\\';

    private $dictPath = '';

    public function __construct($dictPath)
    {
        if (file_exists($dictPath)) {
            $this->dictPath = $dictPath;
            $this->dict = igbinary_unserialize(file_get_contents($dictPath));
        }
    }

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
        $wr = &$this->dict;
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
        $str = igbinary_serialize($this->dict);
        return file_put_contents($this->dictPath, $str);
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
        $l = mb_strlen($str, 'utf-8');
        $r = [];
        for ($i = 0; $i < $l; $i++) {
            $r[] = mb_substr($str, $i, 1, 'utf-8');
        }
        return $r;
    }
}