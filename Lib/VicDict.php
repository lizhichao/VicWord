<?php

/**
 * Add word to dict.
 */

namespace Lizhichao\Word;

class VicDict
{
    private $word = [];

    private $code = 'utf-8';

    private $end = ['\\' => 1];

    private $default_end = ['\\' => 1];

    private $end_key = '\\';

    private $type = 'igb';

    private $dictPath = '';

    public function __construct($type = 'igb')
    {
        $this->type = $type;

        // set default dict path
        $this->dictPath = \defined('\\_VIC_WORD_DICT_PATH_') ? \_VIC_WORD_DICT_PATH_ : \dirname(__DIR__) . "/Data/dict.{$type}";

        if ( ! \file_exists($this->dictPath)) {
            throw new \Exception("Invalid dict file: {$this->dictPath}");
        }

        // check dict type
        switch ($type) {
            case 'igb':
                if ( ! \function_exists('\\igbinary_unserialize')) {
                    throw new \Exception('Requires igbinary PHP extension.');
                }

                $this->word = \igbinary_unserialize(\file_get_contents($this->dictPath));
                break;
            case 'json':
                $this->word = \json_decode(\file_get_contents($this->dictPath), true);
                break;
            default:
                throw new \Exception('Invalid dict type.');
        }
    }

    /**
     * @param string      $word
     * @param null|string $x    词性
     *
     * @return bool
     */
    public function add($word, $x = null)
    {
        $this->end = ['\\x' => $x] + $this->default_end;
        $word      = $this->filter($word);
        if ($word) {
            return $this->merge($word);
        }

        return false;
    }

    public function save()
    {
        if ('igb' === $this->type) {
            $str = \igbinary_serialize($this->word);
        } else {
            $str = \json_encode($this->word);
        }

        return \file_put_contents($this->dictPath, $str);
    }

    private function merge($word)
    {
        $ar = $this->toArr($word);
        $br = $ar;
        $wr = &$this->word;
        foreach ($ar as $i => $v) {
            \array_shift($br);
            if ( ! isset($wr[$v])) {
                $wr[$v] = $this->dict($br, $this->end);

                return true;
            }
            $wr = &$wr[$v];
        }
        if ( ! isset($wr[$this->end_key])) {
            foreach ($this->end as $k => $v) {
                $wr[$k] = $v;
                $wr[$k] = $v;
            }
        }

        return true;
    }

    private function filter($word)
    {
        return \str_replace(["\n", "\t", "\r"], '', \trim($word));
    }

    private function dict($arr, $v, $i = 0)
    {
        if (isset($arr[$i])) {
            return [$arr[$i] => $this->dict($arr, $v, $i + 1)];
        }

        return $v;
    }

    private function toArr($str)
    {
        $l = \mb_strlen($str, $this->code);
        $r = [];
        for ($i = 0; $i < $l; ++$i) {
            $r[] = \mb_substr($str, $i, 1, $this->code);
        }

        return $r;
    }
}
