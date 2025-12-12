<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Implement\UserRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register()
    {
$this->app->bind(
    \App\Repositories\Interfaces\UserRepositoryInterface::class,
    \App\Repositories\Implement\UserRepository::class
);
    }
}
