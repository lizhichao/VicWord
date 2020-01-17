<?php

declare(strict_types = 1);

namespace Kmvan\Participle\Word;

class Insert
{
    private $word = [];

    private $code = 'utf-8';

    private $end = ['\\' => 1];

    private $defaultEnd = ['\\' => 1];

    private $endKey = '\\';

    private $dictType = 'igb';

    private $dictPath = '';

    /**
     * @param array $args
     *                    $args['dictType'] 字典类型，可选，默认为 'igb'
     *                    $args['dictPath'] 字典绝对路径，可选
     */
    public function __construct(array $args = [])
    {
        [
            'dictType' => $this->dictType,
            'dictPath' => $this->dictPath,
        ] = \array_merge([
            'dictType' => 'igb',
            'dictPath' => '',
        ], $args);

        if ( ! $this->dictPath) {
            if ( ! $this->dictType) {
                throw new \Exception('Empty dictionary type.');
            }

            $this->dictPath = \dirname(__DIR__) . "/Data/dict.{$this->dictType}";
        }

        if ( ! \file_exists($this->dictPath)) {
            throw new \Exception("Invalid dictionary file: {$this->dictPath}");
        }

        // check dict type
        switch ($this->dictType) {
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
                throw new \Exception('Invalid dictionary type.');
        }
    }

    /**
     * 添加词.
     *
     * @param string      $word 词
     * @param string|null $x    词性
     */
    public function add(string $word, string $x = null): bool
    {
        $this->end = ['\\x' => $x] + $this->defaultEnd;
        $word      = $this->filter($word);

        if ($word) {
            return $this->merge($word);
        }

        return false;
    }

    /**
     * 保存词典.
     */
    public function save(): bool
    {
        if ('igb' === $this->dictType) {
            $str = \igbinary_serialize($this->word);
        } else {
            $str = \json_encode($this->word, \JSON_UNESCAPED_UNICODE | \JSON_PRETTY_PRINT);
        }

        return (bool) \file_put_contents($this->dictPath, $str);
    }

    private function merge(string $word): bool
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

        if ( ! isset($wr[$this->endKey])) {
            foreach ($this->end as $k => $v) {
                $wr[$k] = $v;
                $wr[$k] = $v;
            }
        }

        return true;
    }

    private function filter(string $word): string
    {
        return \str_replace(["\n", "\t", "\r"], '', \trim($word));
    }

    private function dict(array $arr, array $v, int $i = 0)
    {
        if (isset($arr[$i])) {
            return [$arr[$i] => $this->dict($arr, $v, $i + 1)];
        }

        return $v;
    }

    private function toArr(string $str): array
    {
        $l = \mb_strlen($str, $this->code);
        $r = [];

        for ($i = 0; $i < $l; ++$i) {
            $r[] = \mb_substr($str, $i, 1, $this->code);
        }

        return $r;
    }
}
