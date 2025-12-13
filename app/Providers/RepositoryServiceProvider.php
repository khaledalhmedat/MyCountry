<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Implement\UserRepository;
use App\Repositories\Interfaces\ComplaintRepositoryInterface;
use App\Repositories\Implement\ComplaintRepository;
use App\Repositories\Interfaces\EmployeeRepositoryInterface;
use App\Repositories\Implement\EmployeeRepository;


class RepositoryServiceProvider extends ServiceProvider
{
   public function register(): void
    {
        $this->app->bind(
            UserRepositoryInterface::class,
            UserRepository::class
        );
        
        $this->app->bind(
            ComplaintRepositoryInterface::class,
            ComplaintRepository::class
        );

          $this->app->bind(
            EmployeeRepositoryInterface::class,
            EmployeeRepository::class
        );
    }
}
