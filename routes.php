<?php

namespace Elegance;

Router::add(['elegance.front']);

Router::add([], [
    'assets...' => function () {
        $file = path('public/', ...Request::route());
        Assets::load($file);
        if (File::getEx($file) == 'scss' || File::getEx($file) == 'css')
            Response::content(Scss::compile(Response::getContent()));
        Response::send();
    },
    'page1' => 'page1',
    'page2' => 'page2',
    'page3' => 'page3',
    '' => '>page1'
]);
