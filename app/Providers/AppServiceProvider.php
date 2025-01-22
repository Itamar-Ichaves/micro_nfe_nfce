<?php

namespace App\Providers;

use App\Models\NotaNfe;
use App\Observers\NotaNfeObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        NotaNfe::Observe(NotaNfeObserver::class);
    }
}
