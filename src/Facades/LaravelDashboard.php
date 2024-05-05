<?php

namespace Sicaboy\LaravelDashboard\Facades;

use Illuminate\Support\Facades\Facade;

class LaravelDashboard extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Sicaboy\LaravelDashboard\LaravelDashboard::class;
    }
}
