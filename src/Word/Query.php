<?php

declare(strict_types = 1);

namespace Kmvan\Participle\Word;

class Query
{
    // 字典
    private $dict = [];

    private $end = '\\';

    private $auto = false;

    private $count = 0;

    // 词性
    private $x = '\\x';

    /**
     * @param array $args
     *                    $args['dictType'] 字典类型，可选，默认为 'igb'
     *                    $args['dictPath'] 字典绝对路径，可选
     */
    public function __construct(array $args = [])
    {
        [
            'dictType' => $dictType,
            'dictPath' => $dictPath,
        ] = \array_merge([
            'dictType' => 'igb',
            'dictPath' => '',
        ], $args);

        if ( ! $dictPath) {
            if ( ! $dictType) {
                throw new \Exception('Empty dictionary type.');
            }

            $dictPath = \dirname(__DIR__) . "/Data/dict.{$dictType}";
        }

        if ( ! \file_exists($dictPath)) {
            throw new \Exception("Invalid dictionary file: {$dictPath}");
        }

        // check dict type
        switch ($dictType) {
            case 'igb':
                if ( ! \function_exists('\\igbinary_unserialize')) {
                    throw new \Exception('Requires igbinary PHP extension.');
                }

                $this->dict = \igbinary_unserialize(\file_get_contents($dictPath));

                break;
            case 'json':
                $this->dict = \json_decode(\file_get_contents($dictPath), true);

                break;
            default:
                throw new \Exception('Invalid dictionary type.');
        }
    }

    /**
     * 长度优先切分.
     */
    public function getWord(string $sentence): array
    {
        $this->auto = false;
        $sentence   = $this->filter($sentence);

        return $this->find($sentence);
    }

    /**
     * 细粒度切分.
     */
    public function getShortWord(string $sentence): array
    {
        $this->auto = false;
        $sentence   = $this->filter($sentence);

        return $this->shortfind($sentence);
    }

    /**
     * 自动切分.
     */
    public function getAutoWord(string $sentence): array
    {
        $this->auto = true;
        $sentence   = $this->filter($sentence);

        return $this->autoFind($sentence, ['long' => 1]);
    }

    private function filter(string $sentence): string
    {
        return \strtolower(\trim($sentence));
    }

    private function getD(string &$sentence, int $i): array
    {
        $o = \ord($sentence[$i]);

        if ($o < 128) {
            $d = $sentence[$i];
        } else {
            $o = $o >> 4;

            if (12 === $o) {
                $d = $sentence[$i] . $sentence[++$i];
            } elseif (14 === $o) {
                $d = $sentence[$i] . $sentence[++$i] . $sentence[++$i];
            } elseif (15 === $o) {
                $d = $sentence[$i] . $sentence[++$i] . $sentence[++$i] . $sentence[++$i];
            } else {
                throw new \Exception('Error: unknow charset.');
            }
        }

        return [$d, $i];
    }

    private function autoFind(string $sentence, array $autoInfo = []): array
    {
        if ($autoInfo['long']) {
            return $this->find($sentence, $autoInfo);
        }

        return $this->shortfind($sentence, $autoInfo);
    }

    private function reGet(array &$r, array $autoInfo): void
    {
        $autoInfo['c'] = isset($autoInfo['c']) ? $autoInfo['c']++ : 1;
        $l             = \count($r) - 1;
        $p             = [];
        $sentence      = '';

        for ($i = $l; $i >= 0; --$i) {
            $sentence = $r[$i][0] . $sentence;
            $f        = $r[$i][3];
            \array_unshift($p, $r[$i]);
            unset($r[$i]);

            if (1 === (int) $f) {
                break;
            }
        }

        ++$this->count;
        $l = \strlen($sentence);

        if (isset($r[$i - 1])) {
            $w = $r[$i - 1][1];
        } else {
            $w = 0;
        }

        if (isset($autoInfo['pl']) && $l === (int) $autoInfo['pl']) {
            $r = $p;

            return;
        }

        if ($sentence && $autoInfo['c'] < 3) {
            $autoInfo['pl']   = $l;
            $autoInfo['long'] = ! $autoInfo['long'];
            $sr               = $this->autoFind($sentence, $autoInfo);
            $sr               = \array_map(function (array $v) use ($w): array {
                $v[1] += $w;

                return $v;
            }, $sr);
            $r = \array_merge($r, $this->getGoodWord($p, $sr));
        }
    }

    private function getGoodWord(array $old, array $new): array
    {
        if ( ! $new) {
            return $old;
        }

        if ($this->getUnknowCount($old) > $this->getUnknowCount($new)) {
            return $new;
        }

        return $old;
    }

    private function getUnknowCount(array $ar): int
    {
        $i = 0;

        foreach ($ar as $v) {
            if (0 === (int) $v[3]) {
                $i += \strlen($v[0]);
            }
        }

        return $i;
    }

    private function find(string $sentence, array $autoInfo = []): array
    {
        $len = \strlen($sentence);
        $s   = '';
        $n   = '';
        $j   = 0;
        $r   = [];
        $wr  = [];

        for ($i = 0; $i < $len; ++$i) {
            [$d, $i] = $this->getD($sentence, $i);

            if (isset($wr[$d])) {
                $s .= $d;
                $wr = $wr[$d];
            } else {
                if (isset($wr[$this->end])) {
                    $this->addNotFind($r, $n, $s, $j, $autoInfo);
                    $this->addResult($r, $s, $j, $wr[$this->x]);
                    $n = '';
                }
                $wr = $this->dict;

                if (isset($wr[$d])) {
                    $s  = $d;
                    $wr = $wr[$d];
                } else {
                    $s = '';
                }
            }

            $n .= $d;
            $j = $i;
        }

        if (isset($wr[$this->end])) {
            $this->addNotFind($r, $n, $s, $i, $autoInfo);
            $this->addResult($r, $s, $i, $wr[$this->x]);
        } else {
            $this->addNotFind($r, $n, '', $i, $autoInfo);
        }

        return $r;
    }

    private function addNotFind(array &$r, string $n, string $s, int $i, array $autoInfo = []): void
    {
        if ($n !== $s) {
            $n = \str_replace($s, '', $n);
            $this->addResult($r, $n, $i - \strlen($s), '', 0);

            if ($this->auto) {
                $this->reGet($r, $autoInfo);
            }
        }
    }

    private function shortFind(string $sentence, array $autoInfo = []): array
    {
        $len = \strlen($sentence);
        $s   = '';
        $n   = '';
        $r   = [];
        $wr  = [];

        for ($i = 0; $i < $len; ++$i) {
            $j       = $i;
            [$d, $i] = $this->getD($sentence, $i);

            if (isset($wr[$d])) {
                $s .= $d;
                $wr = $wr[$d];
            } else {
                if (isset($wr[$this->end])) {
                    $this->addNotFind($r, $n, $s, $j, $autoInfo);
                    $this->addResult($r, $s, $j, $wr[$this->x]);
                    $n = '';
                }
                $wr = $this->dict;

                if (isset($wr[$d])) {
                    $s  = $d;
                    $wr = $wr[$d];
                } else {
                    $s = '';
                }
            }

            $n .= $d;

            if (isset($wr[$this->end])) {
                $this->addNotFind($r, $n, $s, $i, $autoInfo);
                $this->addResult($r, $s, $i, $wr[$this->x]);
                $wr = $this->dict;
                $s  = '';
                $n  = '';
            }
        }

        if (isset($wr[$this->end])) {
            $this->addNotFind($r, $n, $s, $i, $autoInfo);
            $this->addResult($r, $s, $i, $wr[$this->x]);
        } else {
            $this->addNotFind($r, $n, '', $i, $autoInfo);
        }

        return $r;
    }

    private function addResult(array &$r, string $k, int $i, string $x, int $find = 1): void
    {
        $r[] = [$k, $i, $x, $find];
    }
}
