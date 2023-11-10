<?php

namespace Elegance;

// php mx install.front

return function () {
    Terminal::run('composer');

    Terminal::run('create.structure');

    Terminal::run('install.index');

    File::copy('#elegance-server/public/favicon.ico', 'public/favicon.ico');

    File::copy('#elegance-energize/source/routers/front.php', 'source/routers/front.php');

    File::copy('#elegance-energize/routes.php', 'routes.php');

    Dir::copy('#elegance-energize/public', 'public');

    Dir::copy('#elegance-energize/view/front', 'view/front');

    Terminal::echo('Front instalado');
};
