<?php

namespace Middleware;

use Closure;
use Elegance\Energize\Page;
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

            if (is_array($content) || is_json($content))
                parent::encaps($content);
            else
                Response::content(Page::renderize($content));

            Response::send();
        } catch (Error | Exception $e) {

            if ($e->getCode() == STS_REDIRECT)
                throw $e;

            if (IS_ENERGIZE) {
                parent::encapsCatch($e);
                Response::type('json');
                Response::send();
            }

            throw new Exception(json_encode([
                'Message' => $e->getMessage(),
                'Code' => $e->getCode(),
                'File' => $e->getFile(),
                'Line' => $e->getLine(),
            ]), STS_TEAPOT);
        }
    }
}
