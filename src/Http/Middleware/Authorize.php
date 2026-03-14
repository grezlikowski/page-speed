<?php

namespace Grezlikowski\PageSpeed\Http\Middleware;

use Closure;
use Grezlikowski\PageSpeed\PageSpeedPanel;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Authorize
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! PageSpeedPanel::check($request)) {
            abort(403);
        }

        return $next($request);
    }
}
