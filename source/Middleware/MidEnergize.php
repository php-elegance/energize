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

            $content = Page::renderize($content);

            if (is_array($content) || is_json($content))
                parent::encaps($content);
            else
                Response::content($content);

            Response::send();
        } catch (Error | Exception $e) {

            if ($e->getCode() == STS_REDIRECT) {

                if (IS_ENERGIZE) {
                    $url = $e->getMessage();
                    $url = !empty($url) ? url($url) : url('.');
                    $response = [
                        'info' => [
                            'elegance' => true,
                            'status' => STS_REDIRECT,
                            'error' => false,
                            'location' => $url,
                        ],
                        'data' => null
                    ];

                    Response::header('Energize-Location', $url);
                    Response::content($response);
                    Response::status(STS_OK);
                    Response::type('json');
                    Response::send();
                }

                throw $e;
            }

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
