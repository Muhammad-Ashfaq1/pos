<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Customers\SaveCustomerRequest;
use App\Models\Customer;
use App\Models\CustomerCreditTransaction;
use App\Repositories\Interface\CustomerRepositoryInterface;
use App\Services\CreditService;
use App\Services\CustomerPortalService;
use App\Support\Currency;
use App\Support\CustomerVehicleSurface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function __construct(
        private readonly CustomerRepositoryInterface $repo
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Customer::class);

        return $this->repo->index();
    }

    public function listing(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Customer::class);

        $validated = $request->validate([
            'draw' => ['nullable', 'integer'],
            'start' => ['nullable', 'integer', 'min:0'],
            'length' => ['nullable', 'integer', 'min:1', 'max:100'],
            'search.value' => ['nullable', 'string', 'max:255'],
            'customer_type' => ['nullable', Rule::in(array_keys(Customer::typeOptions()))],
            'sort' => ['nullable', Rule::in(['latest', 'name', 'visits_high_low', 'value_high_low'])],
            'columns' => ['nullable', 'array'],
            'columns.*.data' => ['nullable', 'string'],
            'order' => ['nullable', 'array'],
            'order.*.column' => ['nullable', 'integer', 'min:0'],
            'order.*.dir' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);

        return response()->json(
            $this->repo->getCustomersListing($validated, $request->user())
        );
    }

    public function edit(Customer $customer, Request $request): JsonResponse
    {
        $this->authorize('update', $customer);

        return response()->json([
            'data' => $this->repo->getCustomerFormData($customer, $request->user()),
        ]);
    }

    public function save(SaveCustomerRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $customer = isset($validated['id'])
            ? Customer::query()->findOrFail($validated['id'])
            : null;

        if ($customer) {
            $this->authorize('update', $customer);
        } else {
            $this->authorize('create', Customer::class);
        }

        $result = $this->repo->store(
            Arr::except($validated, ['id']),
            $customer,
            $request->user(),
        );

        return response()->json([
            'message' => $result['message'],
            'data' => $result['data'],
        ]);
    }

    public function destroy(Customer $customer): JsonResponse
    {
        $this->authorize('delete', $customer);

        $result = $this->repo->destroy($customer);

        return response()->json([
            'message' => $result['message'],
        ]);
    }

    /**
     * Enable portal access and email the customer an activation link.
     */
    public function invitePortal(Customer $customer, CustomerPortalService $portal): JsonResponse
    {
        $this->authorize('update', $customer);

        $portal->invite($customer);

        return response()->json([
            'message' => "Portal invitation sent to {$customer->email}.",
        ]);
    }

    /**
     * Login as this customer and open the customer portal (same pattern as staff impersonate).
     */
    public function impersonatePortal(Customer $customer): RedirectResponse
    {
        $this->authorize('update', $customer);

        $currentUser = auth()->user();

        if (! $currentUser) {
            return back()->with('error', 'You must be signed in to impersonate a customer.');
        }

        if ((int) $customer->tenant_id !== (int) $currentUser->tenant_id) {
            return back()->with('error', 'Customer does not belong to this tenant.');
        }

        session([
            'impersonator_id' => $currentUser->id,
            'impersonator_return_url' => CustomerVehicleSurface::route('customers_index'),
            'impersonating_customer' => true,
        ]);
        session()->forget('customer_api_token');

        Auth::guard('customer')->login($customer);

        return redirect()
            ->route('customer.dashboard')
            ->with('info', "You are now impersonating {$customer->name}.");
    }

    /**
     * Manually adjust a customer's store-credit balance (positive or negative).
     */
    public function adjustCredit(Customer $customer, Request $request, CreditService $credits): JsonResponse
    {
        $this->authorize('update', $customer);

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'not_in:0'],
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $credits->adjust($customer, (float) $data['amount'], $data['reason'], $request->user()?->getAuthIdentifier());

        return response()->json([
            'message' => 'Store credit adjusted.',
            'data' => [
                'credit_balance' => (float) $customer->fresh()->credit_balance,
                'credit_balance_label' => Currency::format((float) $customer->fresh()->credit_balance),
            ],
        ]);
    }

    /**
     * Credit ledger for a customer (staff view).
     */
    public function creditHistory(Customer $customer): JsonResponse
    {
        $this->authorize('view', $customer);

        $transactions = CustomerCreditTransaction::query()
            ->where('customer_id', $customer->getKey())
            ->with('order:id,order_number')
            ->latest()
            ->limit(100)
            ->get()
            ->map(fn (CustomerCreditTransaction $t) => [
                'type' => $t->type,
                'amount' => (float) $t->amount,
                'amount_label' => ((float) $t->amount >= 0 ? '+' : '-').Currency::format(abs((float) $t->amount)),
                'balance_after_label' => Currency::format((float) $t->balance_after),
                'description' => $t->description,
                'order_number' => $t->order?->order_number,
                'created_at_label' => $t->created_at?->format('M j, Y h:i A'),
            ]);

        return response()->json([
            'data' => $transactions,
            'balance_label' => Currency::format((float) $customer->credit_balance),
        ]);
    }
}
