<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\PrimaryCategory;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \App\Models\User::observe(\App\Observers\UserObserver::class);

        View::composer('components.navbar', function ($view) {
            $parentCategories = PrimaryCategory::with(['subCategories' => function ($query) {
                    $query->active()->orderBy('display_order');
                }])
                ->where('is_active', true)
                ->orderBy('display_order')
                ->get();

            $view->with('parentCategories', $parentCategories);
        });
    }
}
