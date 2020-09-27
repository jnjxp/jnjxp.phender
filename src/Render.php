<?php

declare(strict_types=1);

namespace Phender;

use ArrayObject;
use Closure;
use InvalidArgumentException;
use Throwable;

class Render
{
    private string $path;

    private string $name;

    private ArrayObject $data;

    private ArrayObject $helpers;

    public function __construct(string $path, ArrayObject $data = null, ArrayObject $helpers = null)
    {
        $this->path = dirname($path);
        $this->name = basename($path);
        $this->data = $data ?? new ArrayObject();
        $this->helpers = $helpers ?? new ArrayObject();

        foreach ($this->helpers as $name => $closure) {
            if ($closure instanceof Closure) {
                $this->helpers[$name] = $closure->bindTo($this);
            }
        }
    }

    public function __set(string $name, $value) : void
    {
        $this->data[$name] = $value;
    }

    public function __get(string $name)
    {
        if (isset($this->data[$name])) {
            return $this->data[$name];
        }
    }

    public function __isset(string $name) : bool
    {
        return isset($this->data[$name]);
    }

    public function __call(string $name, array $args = [])
    {
        if (! isset($this->helpers[$name])) {
            throw new InvalidArgumentException("Helper not found: $name");
        }
        return call_user_func_array($this->helpers[$name], $args);
    }

    public function __toString() : string
    {
        return $this->render($this->name);
    }

    public function render(string $name = null, array $data = []) : string
    {
        $path = $this->path . '/'. ($name ?? $this->name);
        try {
            ob_start();
            $this->outputTemplate($path, array_merge($this->data->getArrayCopy(), $data));
            $output = ob_get_clean();
        } catch (Throwable $e) {
            ob_end_clean();
            throw $e;
        }
        return (string) $output;
    }

    private function outputTemplate(string $templatePath, array $data = []) : void
    {
        extract($data);
        require $templatePath;
    }
}
