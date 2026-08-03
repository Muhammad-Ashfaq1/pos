<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\User;
use App\Services\CustomerPortalService;
use App\Support\AccountSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AccountSettingsController extends Controller
{
    public function profile(Request $request): View
    {
        return view('account-settings.index', $this->viewData($request, 'profile'));
    }

    public function password(Request $request): View
    {
        return view('account-settings.index', $this->viewData($request, 'password'));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $account = $this->account($request);

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:75'],
            'last_name' => ['required', 'string', 'max:75'],
            'phone' => ['nullable', 'string', 'max:30'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:2048'],
        ]);

        $account->fill([
            'name' => AccountSettings::combineName($data['first_name'], $data['last_name']),
            'phone' => $data['phone'] ?? null,
        ])->save();

        if ($request->hasFile('avatar')) {
            AccountSettings::storeAvatar($account, $request->file('avatar'));
        }

        return redirect()->route('account.profile')->with('success', 'Profile updated.');
    }

    public function updatePassword(Request $request, CustomerPortalService $portal): RedirectResponse
    {
        $account = $this->account($request);

        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if ($account instanceof Customer) {
            $portal->changePassword($account, $data['current_password'], $data['password']);
        } else {
            if (! Hash::check($data['current_password'], (string) $account->password)) {
                throw ValidationException::withMessages([
                    'current_password' => 'Your current password is incorrect.',
                ]);
            }

            $account->forceFill([
                'password' => $data['password'],
            ])->save();
        }

        return redirect()->route('account.password')->with('success', 'Password updated.');
    }

    private function account(Request $request): User|Customer
    {
        $customer = auth('customer')->user();

        if ($customer instanceof Customer) {
            return $customer;
        }

        $user = $request->user();

        if (! $user instanceof User) {
            abort(401);
        }

        return $user;
    }

    /**
     * @return array<string, mixed>
     */
    private function viewData(Request $request, string $active): array
    {
        return AccountSettings::viewData(
            account: $this->account($request),
            layout: $this->layout(),
            active: $active,
        );
    }

    private function layout(): string
    {
        if (auth('customer')->check()) {
            return 'layouts.customer-portal';
        }

        if (auth()->user()?->isEmployee()) {
            return 'layouts.employee-portal';
        }

        return 'layouts.app';
    }
}
