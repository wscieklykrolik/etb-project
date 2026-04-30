<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserRoleController extends Controller
{
    public function update(Request $request, User $user): JsonResponse
    {
        $this->authorize('assign-roles');

        $validated = $request->validate([
            'role' => ['required', Rule::in(User::roles())],
        ]);

        $user->update(['role' => $validated['role']]);

        match ($validated['role']) {
            User::ROLE_ATHLETE => $user->athleteProfile()->firstOrCreate([], [
                'performance_parameters' => [],
                'playbook' => [],
                'opponents_intel' => [],
            ]),
            User::ROLE_FAN => $user->fanProfile()->firstOrCreate([], [
                'can_buy_tickets' => true,
                'can_buy_merch' => true,
            ]),
            User::ROLE_EMPLOYEE => $user->employeeProfile()->firstOrCreate([], [
                'can_manage_articles' => true,
                'can_write_database' => true,
                'can_read_database' => true,
            ]),
            default => null,
        };

        return response()->json([
            'message' => 'Rola użytkownika została zaktualizowana.',
            'user' => $user->fresh(),
        ]);
    }
}
