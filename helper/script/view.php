<?php

use Elegance\Server\View;
use Elegance\Server\ViewRender\ViewRenderCss;
use Elegance\ViewRender\ViewRenderVue;

View::suportedSet('scss', '_style.scss', ViewRenderCss::class);

View::suportedSet('vue', null, ViewRenderVue::class);

View::autoImportViewEx('scss');
