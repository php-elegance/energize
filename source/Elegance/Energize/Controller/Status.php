<?php

namespace Elegance\Energize\Controller;

use Elegance\Energize\Page;
use Elegance\Server\Controller\Status as ServerStatus;
use Elegance\Server\Response;
use Error;
use Exception;

class Status extends ServerStatus
{
    /** Mensagem de erro generica */
    function error(Error|Exception $e)
    {
        if ($e->getCode() == STS_TEAPOT) {
            $recivedE = $e;
            $scheme = json_decode($e->getMessage(), true);

            if (env('DEV')) {
                Response::header('Elegance-Error-Message', $scheme['Message']);
                Response::header('Elegance-Error-Code', $scheme['Code']);
                Response::header('Elegance-Error-File', $scheme['File']);
                Response::header('Elegance-Error-Line', $scheme['Line']);
            }

            try {
                $status = $scheme['Code'];

                if (!is_httpStatus($status))
                    $status = !is_class($e, Error::class) ? STS_BAD_REQUEST : STS_INTERNAL_SERVER_ERROR;

                $content = $scheme['Message'];

                if (empty($content))
                    $content = env("STM_$status", null);

                $content = Page::renderize($content);

                Response::status($status);
                Response::content($content);
                Response::cache(false);

                Response::send();
            } catch (Error | Exception $e) {

                if (env('DEV')) {
                    Response::header('Energize-Error-Message', $e->getMessage());
                    Response::header('Energize-Error-Code', $e->getCode());
                    Response::header('Energize-Error-File', $e->getFile());
                    Response::header('Energize-Error-Line', $e->getLine());
                }

                Response::header('Elegance-Error-Line', $scheme['Line']);
                $e = new Exception($scheme['Message'], $scheme['Code'], $recivedE);
            }
        }

        parent::error($e);
    }
}
