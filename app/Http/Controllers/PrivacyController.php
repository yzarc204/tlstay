<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class PrivacyController extends Controller
{
    /**
     * Display the privacy policy page.
     */
    public function index()
    {
        return Inertia::render('Privacy/Index');
    }
}
