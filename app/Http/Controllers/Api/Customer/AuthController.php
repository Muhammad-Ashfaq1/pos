<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\Customer\CustomerResource;
use App\Models\Customer;
use App\Services\CustomerPortalService;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        private readonly CustomerPortalService $portal,
        private readonly TenantContext $tenantContext,
    ) {}

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'shop' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:120'],
        ]);

        $tenant = $this->portal->findTenantBySlug($data['shop']);

        if (! $tenant) {
            throw ValidationException::withMessages(['shop' => 'Shop not found.']);
        }

        $customer = $this->portal->findCustomerForLogin($tenant, $data['email']);

        if (! $customer || ! $customer->hasPortalAccess() || ! Hash::check($data['password'], $customer->password)) {
            throw ValidationException::withMessages([
                'email' => 'The provided credentials are incorrect.',
            ]);
        }

        // Scope subsequent reads (e.g. currency formatting) to this shop.
        $this->tenantContext->initialize($tenant);

        return $this->tokenResponse($customer, $data['device_name'] ?? 'portal');
    }

    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'shop' => ['required', 'string'],
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'device_name' => ['nullable', 'string', 'max:120'],
        ]);

        $tenant = $this->portal->findTenantBySlug($data['shop']);

        if (! $tenant) {
            throw ValidationException::withMessages(['shop' => 'Shop not found.']);
        }

        $this->tenantContext->initialize($tenant);
        $customer = $this->portal->register($tenant, $data);

        return $this->tokenResponse($customer, $data['device_name'] ?? 'portal', 201);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'shop' => ['required', 'string'],
            'email' => ['required', 'email'],
        ]);

        $tenant = $this->portal->findTenantBySlug($data['shop']);

        if ($tenant) {
            $this->portal->sendResetLink($tenant, $data['email']);
        }

        // Always succeed to avoid leaking which emails are registered.
        return response()->json([
            'message' => 'If an account exists for that email, a reset link has been sent.',
        ]);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'shop' => ['required', 'string'],
            'email' => ['required', 'email'],
            'token' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $tenant = $this->portal->findTenantBySlug($data['shop']);

        if (! $tenant) {
            throw ValidationException::withMessages(['shop' => 'Shop not found.']);
        }

        $this->tenantContext->initialize($tenant);
        $customer = $this->portal->resetPassword($tenant, $data['email'], $data['token'], $data['password']);

        return response()->json([
            'message' => 'Password updated successfully. You can now sign in.',
            'data' => new CustomerResource($customer->loadMissing('tenant')),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        /** @var Customer $customer */
        $customer = $request->user();
        $customer->currentAccessToken()?->delete();

        return response()->json(['message' => 'Signed out.']);
    }

    private function tokenResponse(Customer $customer, string $deviceName, int $status = 200): JsonResponse
    {
        $token = $customer->createToken($deviceName)->plainTextToken;

        return response()->json([
            'message' => 'Authenticated.',
            'token' => $token,
            'data' => new CustomerResource($customer->loadMissing('tenant')),
        ], $status);
    }
}
