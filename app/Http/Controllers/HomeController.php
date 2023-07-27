<?php

namespace App\Http\Controllers;

use App\Models\CallHistory;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $callHistories = CallHistory::all(); // Fetch all call history records from the database.

        return view('home', compact('callHistories'));
    }
}
