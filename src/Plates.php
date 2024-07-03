<?php

declare(strict_types=1);

namespace Phico\View\Plates;

use League\Plates\Engine;
use Phico\View\{ViewException, ViewInterface};


class Plates implements ViewInterface
{
    private Engine $engine;
    private array $options = [
        'file_extension' => '.plates.php',
        'default_path' => 'tests/views',
        'folders' => [],
        'functions' => [],
        'extensions' => [],
    ];


    public function __construct(array $overrides = [])
    {
        // apply default options overriding with known overrides
        foreach ($this->options as $k => $v) {
            $this->options[$k] = (isset($overrides[$k])) ? $overrides[$k] : $v;
        }

        $this->engine = new Engine(path($this->options['default_path']), ltrim($this->options['file_extension'], '.'));
        // @TODO ensure request is unique
        // $this->engine->loadExtension(new \League\Plates\Extension\URI($_SERVER['PATH_INFO']));

        foreach ($this->options['folders'] as $name => $path) {
            $this->engine->addFolder($name, path($path));
        }
        foreach ($this->options['functions'] as $name => $callable) {
            $this->engine->registerFunction($name, $callable);
        }
        foreach ($this->options['extensions'] as $class) {
            $this->engine->loadExtension($class);
        }
    }
    public function render(string $template, array $data = [], bool $is_string = false): string
    {
        try {

            return $this->engine->render($template, $data);

        } catch (\Throwable $th) {

            throw new ViewException(sprintf('%s in file %s line %d', $th->getMessage(), $th->getFile(), $th->getLine()), 5050, $th);

        }
    }
    public function string(string $code, array $data = []): ?string
    {
        throw new \BadMethodCallException('Plates cannot render strings');
    }
    public function template(string $template, array $data = []): string
    {
        return $this->render($template, $data, false);
    }
}
