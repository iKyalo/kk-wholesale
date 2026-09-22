<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TransfersController extends Controller
{
    public function index()
    {
        $transfers = [];
        return view('transfers.index', compact('transfers'));
    }
}
