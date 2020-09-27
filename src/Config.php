<?php

declare(strict_types=1);

namespace Phender;

use InvalidArgumentException;

class Config
{
    const OPTIONS = 'i:v:h:';
    const LONGOPTS = ['include:', 'var:', 'helpers:'];
    const SEP = ':';

    private const DATA_INC = ['include', 'i'];
    private const DATA_VAR = ['var', 'v'];
    private const HELPERS  = ['helpers', 'h'];

    private const VIEW_POS_IDX = 0;
    private const LAYOUT_POS_IDX = 1;

    private array $opt;

    private array $pos;

    private array $data;

    private array $helpers;

    private function __construct(array $opt, array $pos)
    {
        $this->opt = $opt;
        $this->pos = $pos;
    }

    public static function fromGetOpt(array $argv) : self
    {
        $optind = null;
        $opt = getopt(self::OPTIONS, self::LONGOPTS, $optind);
        $pos = array_slice($argv, $optind);
        return new self($opt, $pos);
    }

    public function getView() : string
    {
        if (count($this->pos) < 1) {
            throw new InvalidArgumentException('No view specified. usage: phender VIEW');
        }
        return $this->pos[self::VIEW_POS_IDX];
    }

    public function getLayout() : ?string
    {
        return $this->pos[self::LAYOUT_POS_IDX] ?? null;
    }

    public function getData() : array
    {
        $this->data = [];
        $this->parseIncludes();
        $this->parseVariables();
        return $this->data;
    }

    public function getHelpers() : array
    {
        $this->helpers = [];
        foreach (self::HELPERS as $key) {
            if (isset($this->opt[$key])) {
                foreach ((array) $this->opt[$key] as $path) {
                    $this->parseHelpers($path);
                }
            }
        }
        return $this->helpers;
    }

    private function parseHelpers(string $path) : void
    {
        if (! is_file($path)) {
            throw new InvalidArgumentException("Invalid --helper: $path");
        }

        $this->helpers = array_merge($this->helpers, require $path);
    }

    private function parseIncludes() : void
    {
        foreach (self::DATA_INC as $key) {
            if (isset($this->opt[$key])) {
                foreach ((array) $this->opt[$key] as $spec) {
                    $this->parseInc($spec);
                }
            }
        }
    }

    private function parseVariables() : void
    {
        foreach (self::DATA_VAR as $key) {
            if (isset($this->opt[$key])) {
                foreach ((array) $this->opt[$key] as $spec) {
                    $this->parseVar($spec);
                }
            }
        }
    }

    private function parseInc(string $spec) : void
    {
        @list($name, $path) = explode(self::SEP, $spec, 2);

        if (! $path) {
            throw new InvalidArgumentException('--include must be int he form: NAME:path/to/file');
        }

        if (! is_file($path)) {
            throw new InvalidArgumentException("Invalid --include: $path");
        }

        $this->data[$name] = require $path;
    }

    private function parseVar(string $spec) : void
    {
        @list($name, $value) = explode(self::SEP, $spec, 2);
        $this->data[$name] = $value ?? true;
    }
}
