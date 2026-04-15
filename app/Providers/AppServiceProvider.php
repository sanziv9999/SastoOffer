<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Category;
use App\Models\Deal;

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

        View::composer('components.footer', function ($view) {
            $footerMainCategories = Category::query()
                ->select('categories.*')
                ->addSelect([
                    'active_deals_count' => Deal::query()
                        ->selectRaw('count(*)')
                        ->join('categories as deal_categories', 'deal_categories.id', '=', 'deals.category_id')
                        ->where('deals.status', 'active')
                        ->whereNull('deals.deleted_at')
                        ->where(function ($query) {
                            $query->whereColumn('deal_categories.id', 'categories.id')
                                ->orWhereColumn('deal_categories.parent_id', 'categories.id');
                        }),
                ])
                ->whereNull('categories.parent_id')
                ->where('categories.is_active', true)
                ->orderByDesc('active_deals_count')
                ->orderBy('categories.display_order')
                ->orderBy('categories.name')
                ->limit(5)
                ->get();

            $view->with('footerMainCategories', $footerMainCategories);
        });
    }
}
