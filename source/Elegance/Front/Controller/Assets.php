<?php

namespace Elegance\Front\Controller;

use Elegance\Core\File;
use Elegance\Front\Scss;
use Elegance\Server\Controller\Assets as ControllerAssets;
use Elegance\Server\Response;
use Elegance\Server\View;

class Assets extends ControllerAssets
{
    protected function send($path): void
    {
        if (File::getEx($path) == 'scss' || File::getEx($path) == 'css') {
            $content = View::render("=$path");
            $content = Scss::compile($content);
            Response::content($content);
            Response::type('css');
            Response::send();
        } else {
            parent::send($path);
        }
    }
}
