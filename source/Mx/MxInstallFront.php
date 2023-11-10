<?php

namespace Mx;

use Elegance\Core\Dir;
use Elegance\Core\File;
use Elegance\Core\Import;

class MxInstallFront extends Mx
{
    function __invoke()
    {
        self::run('composer');

        if (!File::check('routes.php')) {
            File::create('routes.php', Import::content('#elegance-front/view/template/mx/routes.txt'));
            self::echo('Arquivo routes.php criado');
        }

        self::run('create.structure');

        Dir::copy('#elegance-front/library/assets', 'library/assets');

        // Dir::copy('#elegance-front/view/front', 'view/front');

        self::echo('Front instalado');
    }
}
