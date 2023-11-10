<?php

namespace Mx;

use Elegance\Core\Dir;
use Elegance\Core\File;
use Elegance\Core\Import;

class MxInstallEnergize extends Mx
{
    function __invoke()
    {
        self::run('composer');

        if (!File::check('routes.php')) {
            File::create('routes.php', Import::content('#elegance-energize/view/template/mx/routes.txt'));
            self::echo('Arquivo routes.php criado');
        }

        self::run('create.structure');

        Dir::copy('#elegance-energize/library/assets', 'library/assets');

        Dir::copy('#elegance-energize/view/template/page', 'view/template/page');

        self::echo('Energize instalado');
    }
}
