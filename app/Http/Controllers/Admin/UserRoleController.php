<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserRoleController extends Controller
{
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'string', Rule::in(User::roles())],
        ]);

        $user->update(['role' => $validated['role']]);

        return back()->with('success', 'Changes saved successfully');
    }
}
