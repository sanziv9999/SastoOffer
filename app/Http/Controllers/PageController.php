<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class PageController extends Controller
{
    public function home()
    {
        return view('home');
    }

    public function search(\Illuminate\Http\Request $request)
    {
        $query = $request->query('q', '');
        $category = $request->query('category', 'all');
        $sort = $request->query('sort', 'relevance');
        $featured = $request->query('featured') === 'true';
        $type = $request->query('type', 'all');
        $minPrice = (int)$request->query('minPrice', 0);
        $maxPrice = (int)$request->query('maxPrice', 1000);

        // Mock data for deals - matching the structure used in home components
        $deals = [
            [
                'id' => '1',
                'title' => '50% Off Luxury 5-Course Dinner for Two',
                'categorySlug' => 'food-dining',
                'categoryName' => 'Restaurants',
                'originalPrice' => 200,
                'discountedPrice' => 100,
                'discountPercentage' => 50,
                'image' => 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=600&auto=format',
                'featured' => true,
                'type' => 'percentage',
                'location' => 'Kathmandu',
                'timeLeft' => '2 days'
            ],
            [
                'id' => '2',
                'title' => 'Luxury Spa Day Package - 30% Off',
                'categorySlug' => 'beauty-spa',
                'categoryName' => 'Beauty & Spa',
                'originalPrice' => 300,
                'discountedPrice' => 210,
                'discountPercentage' => 30,
                'image' => 'https://images.unsplash.com/photo-1544161515-4ab6ce6db874?w=600&auto=format',
                'featured' => true,
                'type' => 'percentage',
                'location' => 'Lalitpur',
                'timeLeft' => '5 hours'
            ],
            [
                'id' => '3',
                'title' => 'Guided Mountain Hiking Tour - BOGO',
                'categorySlug' => 'activities-events',
                'categoryName' => 'Activities',
                'originalPrice' => 150,
                'discountedPrice' => 75,
                'discountPercentage' => 50,
                'image' => 'https://images.unsplash.com/photo-1551632811-561732d1e306?w=600&auto=format',
                'featured' => false,
                'type' => 'bogo',
                'location' => 'Pokhara',
                'timeLeft' => '1 day'
            ],
            [
                'id' => '4',
                'title' => 'iPhone 15 Pro Max Deal',
                'categorySlug' => 'electronics',
                'categoryName' => 'Electronics',
                'originalPrice' => 1200,
                'discountedPrice' => 1050,
                'discountPercentage' => 12,
                'image' => 'https://images.unsplash.com/photo-1695048133142-1a20484d256e?w=600&auto=format',
                'featured' => true,
                'type' => 'fixed',
                'location' => 'Kathmandu',
                'timeLeft' => '3 days'
            ],
            [
                'id' => '5',
                'title' => 'Advanced React Course',
                'categorySlug' => 'education',
                'categoryName' => 'Online Courses',
                'originalPrice' => 99,
                'discountedPrice' => 29,
                'discountPercentage' => 70,
                'image' => 'https://images.unsplash.com/photo-1633356122544-f134324a6cee?w=600&auto=format',
                'featured' => false,
                'type' => 'percentage',
                'location' => 'Online',
                'timeLeft' => '10 hours'
            ],
            [
                'id' => '6',
                'title' => 'Luxury Hotel Stay Pokhara',
                'categorySlug' => 'travel',
                'categoryName' => 'Travel',
                'originalPrice' => 500,
                'discountedPrice' => 350,
                'discountPercentage' => 30,
                'image' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=600&auto=format',
                'featured' => true,
                'type' => 'percentage',
                'location' => 'Pokhara',
                'timeLeft' => '4 days'
            ]
        ];

        // Simple mock filtering
        $filteredDeals = array_filter($deals, function($deal) use ($query, $category, $featured, $type, $minPrice, $maxPrice) {
            $match = true;
            
            if ($query && !str_contains(strtolower($deal['title']), strtolower($query))) {
                $match = false;
            }
            
            if ($category !== 'all' && $deal['categorySlug'] !== $category) {
                $match = false;
            }
            
            if ($featured && !$deal['featured']) {
                $match = false;
            }
            
            if ($type !== 'all' && $deal['type'] !== $type) {
                $match = false;
            }
            
            if ($deal['discountedPrice'] < $minPrice || $deal['discountedPrice'] > $maxPrice) {
                $match = false;
            }
            
            return $match;
        });

        // Simple mock sorting
        usort($filteredDeals, function($a, $b) use ($sort) {
            switch ($sort) {
                case 'priceAsc':
                    return $a['discountedPrice'] <=> $b['discountedPrice'];
                case 'priceDesc':
                    return $b['discountedPrice'] <=> $a['discountedPrice'];
                case 'discountDesc':
                    return $b['discountPercentage'] <=> $a['discountPercentage'];
                // For simplicity, others are relevance (default order)
                default:
                    return 0;
            }
        });

        $categories = [
            ['id' => '1', 'name' => 'Restaurants', 'slug' => 'food-dining'],
            ['id' => '2', 'name' => 'Beauty & Spa', 'slug' => 'beauty-spa'],
            ['id' => '3', 'name' => 'Activities', 'slug' => 'activities-events'],
            ['id' => '4', 'name' => 'Travel', 'slug' => 'travel'],
            ['id' => '5', 'name' => 'Electronics', 'slug' => 'electronics'],
            ['id' => '6', 'name' => 'Services', 'slug' => 'services'],
            ['id' => '7', 'name' => 'Health & Fitness', 'slug' => 'health-fitness'],
            ['id' => '8', 'name' => 'Education', 'slug' => 'education'],
        ];

        return view('search', [
            'deals' => $filteredDeals,
            'categories' => $categories,
            'query' => $query,
            'currentCategory' => $category,
            'sortBy' => $sort,
            'isFeatured' => $featured,
            'dealType' => $type,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
        ]);
    }

    public function forgotPassword()
    {
        return Inertia::render('ForgotPasswordPage');
    }

    public function checkout()
    {
        return Inertia::render('CheckoutPage');
    }

    public function notFound()
    {
        return Inertia::render('NotFound');
    }
}
