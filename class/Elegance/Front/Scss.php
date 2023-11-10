<?php

namespace Elegance\Front;

use Elegance\Server\ViewRender\ViewRenderCss;
use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\OutputStyle;

abstract class Scss
{
    static function compile($style)
    {
        $style = ViewRenderCss::minify($style);

        $scssCompiler = (new Compiler());
        $scssCompiler->setOutputStyle(OutputStyle::COMPRESSED);
        return $scssCompiler->compileString($style)->getCss();
    }
}
