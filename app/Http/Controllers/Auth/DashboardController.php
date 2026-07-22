<?php

namespace App\Http\Controllers\Auth;

use App\Domain\Auth\DashboardResolver;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request, DashboardResolver $resolver): RedirectResponse
    {
        return redirect()->to($resolver->destination($request->user(), $request->session()));
    }
}
