<?php

namespace Middleware;

use Closure;

/** energize */
class MidEnergize
{
    function __invoke(Closure $next)
    {
        return $next();
    }
}
