<?php

namespace Elegance\Energize;

use Elegance\Server\View;
use Elegance\Server\ViewRender\ViewRenderCss;
use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\OutputStyle;

abstract class Scss
{
    protected static $importInCompile = null;

    /** Define arquivos que devem ser utilizados na compilação do SCSS */
    static function useInCompile(string $view, $prepare = [])
    {
        self::$importInCompile = [$view, $prepare];
    }

    /** Compila uma string SCSS em uma string CSS */
    static function compile($style)
    {
        if (!is_null(self::$importInCompile))
            $style = View::render(...self::$importInCompile) . $style;

        $style = ViewRenderCss::minify($style);

        $scssCompiler = (new Compiler());
        $scssCompiler->setOutputStyle(OutputStyle::COMPRESSED);
        return $scssCompiler->compileString($style)->getCss();
    }
}
