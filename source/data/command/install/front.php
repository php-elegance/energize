<?php

namespace Elegance;

// php mx install.front

return function () {
    Terminal::run('composer');

    Terminal::run('install.structure');

    Terminal::run('install.index');

    File::copy('#elegance-server/library/assets/favicon.ico', 'library/assets/favicon.ico');

    Dir::copy('#elegance-front/library/assets', 'library/assets');

    Dir::copy('#elegance-front/front', 'front');

    Terminal::echo('Front instalado');
};
