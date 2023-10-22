<?php

namespace Elegance;

// php mx install.front

return function () {
    Terminal::run('composer');

    Terminal::run('install.structure');

    Terminal::run('install.index');

    File::copy('#elegance-server/assets/favicon.ico', 'assets/favicon.ico');

    Dir::copy('#elegance-front/assets', 'assets');

    Dir::copy('#elegance-front/view/front', 'view/front');

    Terminal::echo('Front instalado');
};
