<?php

use Elegance\Server\Request;

/** Se a requisição é uma solicitação ENERGIZE */
define('IS_ENERGIZE', !IS_TERMINAL && Request::header('Energize-Request'));

/** Se a requisição é uma solicitação de fragmento */
define('IS_FRAGMENT', IS_ENERGIZE && Request::header('Energize-Fragment'));
