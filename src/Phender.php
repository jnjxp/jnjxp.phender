<?php

declare(strict_types=1);

namespace Phender;

use InvalidArgumentException;
use ArrayObject;

class Phender
{
    const CONTENT = 'content';

    private string $view;

    private ArrayObject $data;

    private ?string $layout;

    private ArrayObject $helpers;

    public function __construct(string $view, array $data = [], string $layout = null, array $helpers = [])
    {
        $this->view     = $view;
        $this->data     = new ArrayObject($data);
        $this->layout   = $layout;
        $this->helpers  = new ArrayObject($helpers);
    }

    public static function fromConfig(Config $config) : self
    {
        return new self(
            $config->getView(),
            $config->getData(),
            $config->getLayout(),
            $config->getHelpers()
        );
    }

    public function __invoke() : void
    {
        echo $this;
    }

    public function __toString() : string
    {
        $output = $this->render($this->view);

        if ($this->layout) {
            $this->data[self::CONTENT] = (string) $output;
            $output = $this->render($this->layout);
        }

        return (string) $output;
    }

    private function render(string $script) : Render
    {
        return new Render($script, $this->data, $this->helpers);
    }
}
