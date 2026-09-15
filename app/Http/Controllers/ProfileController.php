<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $user->loadMissing(['faculty:id,name', 'option:id,name', 'promotion:id,name']);

        return view('profile.show', compact('user'));
    }
}
