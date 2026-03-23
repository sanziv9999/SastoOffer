<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Category;

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

        View::composer(['components.navbar', 'components.layout'], function ($view) {
            // Top-level categories
            $parentCategories = Category::with(['children' => function ($query) {
                    $query->where('is_active', true)->orderBy('display_order');
                }])
                ->whereNull('parent_id')
                ->where('is_active', true)
                ->orderBy('display_order')
                ->get();

            $view->with('parentCategories', $parentCategories);
        });
    }
}
