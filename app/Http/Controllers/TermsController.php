<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class TermsController extends Controller
{
    /**
     * Display the terms of service page.
     */
    public function index()
    {
        return Inertia::render('Terms/Index');
    }
}
