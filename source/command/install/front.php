<?php

namespace Elegance;

// php mx install.front

return function () {
    Terminal::run('composer');

    Terminal::run('create.structure');

    Terminal::run('install.index');

    File::copy('#elegance-server/public/favicon.ico', 'public/favicon.ico');

    File::copy('#elegance-front/source/routers/front.php', 'source/routers/front.php');

    Dir::copy('#elegance-front/public', 'public');

    Dir::copy('#elegance-front/view/front', 'view/front');

    Terminal::echo('Front instalado');
};
