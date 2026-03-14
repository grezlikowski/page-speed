<?php

namespace Grezlikowski\PageSpeed;

use Closure;
use Illuminate\Support\Facades\Gate;

class PageSpeedPanel
{
    /**
     * The callback that should be used to authenticate PageSpeed panel users.
     *
     * @var (Closure(mixed): bool)|null
     */
    public static ?Closure $authUsing = null;

    /**
     * Register the PageSpeed panel authorization callback.
     *
     * @param  Closure(mixed): bool  $callback
     */
    public static function auth(Closure $callback): static
    {
        static::$authUsing = $callback;

        return new static;
    }

    /**
     * Determine if the given request can access the PageSpeed panel.
     */
    public static function check(mixed $request): bool
    {
        if (static::$authUsing) {
            return (static::$authUsing)($request);
        }

        $gate = config('page-speed.gate', 'viewPageSpeed');

        if (Gate::has($gate)) {
            return Gate::check($gate);
        }

        return true;
    }
}
