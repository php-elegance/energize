<?php

namespace Middleware;

use Closure;
use Elegance\Energize\Page;
use Elegance\Server\Request;
use Elegance\Server\Response;
use Error;
use Exception;

/** energize */
class MidEnergize extends MidJson
{
    function __invoke(Closure $next)
    {
        try {
            $content = $next();
            $content = Page::renderize($content);
        } catch (Error | Exception $e) {
            throw new Exception(json_encode([
                'Message' => $e->getMessage(),
                'Code' => $e->getCode(),
                'File' => $e->getFile(),
                'Line' => $e->getLine(),
            ]), STS_TEAPOT);
        }
        Response::content($content);
        Response::send();
    }
}
