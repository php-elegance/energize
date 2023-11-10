<?php

namespace Middleware;

use Closure;

/** front */
class MidFront
{
    function __invoke(Closure $next)
    {
        return $next();
    }
}
