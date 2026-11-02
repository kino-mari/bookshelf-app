<?php

namespace App\Providers;

use App\Models\Book;
use App\Models\ReadingPlan;
use App\Policies\BookPolicy;
use App\Policies\ReadingPlanPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Book::class => BookPolicy::class,
        ReadingPlan::class => ReadingPlanPolicy::class,
    ];

    public function boot(): void
    {
        //
    }
}
