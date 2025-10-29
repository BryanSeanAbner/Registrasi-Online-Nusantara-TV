<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\EventRepositoryInterface;
use App\Repositories\Eloquent\EloquentEventRepository;
use App\Repositories\Contracts\FormFieldRepositoryInterface;
use App\Repositories\Eloquent\EloquentFormFieldRepository;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(EventRepositoryInterface::class, EloquentEventRepository::class);
        $this->app->bind(FormFieldRepositoryInterface::class, EloquentFormFieldRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        // Force HTTPS in production only
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }
        
        Carbon::setLocale('id');
    }
}
