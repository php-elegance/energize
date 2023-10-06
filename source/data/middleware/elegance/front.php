<?php

// middleware elegance.front

use Elegance\Cif;
use Elegance\Front;
use Elegance\Request;
use Elegance\Response;

return function ($next) {
    try {
        return Front::solve($next());
    } catch (Exception | Error $e) {

        if ($e->getCode() == STS_REDIRECT) {
            $url = $e->getMessage();
        } elseif (IS_GET && env('FRONT_ERROR_PAGE')) {
            $url = url(env('FRONT_ERROR_PAGE'), ['error' => $e->getCode()]);
        } else {
            $url = false;
        }

        if ($url) {
            if ($e->getCode() != STS_REDIRECT) {
                $info = [
                    'code' => $e->getCode(),
                    'url' => url(true)
                ];
                $url = url($url, ['info' => Cif::on($info, 'E')]);
            }

            if (Request::header('Front-Request')) {
                Response::cache(false);
                Response::header('Front-Location', $url);
                throw new Exception('', STS_OK);
            }
            redirect($url);
        }

        throw $e;
    }
};
