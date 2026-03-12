<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class SuperAdminController extends Controller
{
    public function index()
    {
        return Inertia::render('SuperAdminDashboard', [
            'stats' => [],
            'databaseStatus' => [],
            'securityStatus' => [],
            'paymentStatus' => [],
            'adminsList' => [],
        ]);
    }

    public function admins()
    {
        return Inertia::render('SuperAdminDashboard', [
            'stats' => [],
            'databaseStatus' => [],
            'securityStatus' => [],
            'paymentStatus' => [],
            'adminsList' => [],
        ]);
    }

    public function analytics()
    {
        return Inertia::render('SuperAdminDashboard', [
            'stats' => [],
            'databaseStatus' => [],
            'securityStatus' => [],
            'paymentStatus' => [],
            'adminsList' => [],
        ]);
    }
}
