<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class PageController extends Controller
{
    public function home()
    {
        return Inertia::render('HomePage');
    }

    public function search()
    {
        return Inertia::render('SearchPage');
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
