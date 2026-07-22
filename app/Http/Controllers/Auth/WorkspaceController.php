<?php

namespace App\Http\Controllers\Auth;

use App\Domain\Audit\AuditLogger;
use App\Domain\Auth\DashboardResolver;
use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WorkspaceController extends Controller
{
    public function index(Request $request, DashboardResolver $resolver): View|RedirectResponse
    {
        $roles = $request->user()->roles()->orderBy('display_name')->get();

        if ($roles->count() === 1) {
            return redirect()->to($resolver->switchTo($request->user(), $request->session(), $roles->first()->name));
        }

        return view('auth.workspace', ['roles' => $roles]);
    }

    public function switch(Request $request, DashboardResolver $resolver, AuditLogger $audit): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'string', Rule::in(Role::NAMES)],
        ]);

        $destination = $resolver->switchTo(
            $request->user(),
            $request->session(),
            $validated['role'],
        );
        $audit->record('workspace_switched', $request->user(), $request->user(), ['role' => $validated['role']]);

        return redirect()->to($destination);
    }
}
