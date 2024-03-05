<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Component;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        //Get Count Of the Total Available Users
        $userCount = User::count();
        //Get Count Of the Total Available Components
        $componentsCount = Component::count();

        return view('dashboard.index',compact('userCount','componentsCount'));
    }
}
