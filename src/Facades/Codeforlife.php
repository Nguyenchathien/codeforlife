<?php

namespace NCH\Codeforlife\Facades;

use Illuminate\Support\Facades\Facade;

class Codeforlife extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'codeforlife';
    }
}
