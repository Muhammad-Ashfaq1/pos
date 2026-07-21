<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\Employee\Cards\SaveCardRequest;
use App\Models\Card;
use App\Repositories\Interface\CardRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CardController extends Controller
{
    public function __construct(
        private readonly CardRepositoryInterface $repo
    ) {}

    public function index(): RedirectResponse
    {
        $this->authorize('viewAny', Card::class);

        return redirect()->route('employee.cards.type', Card::TYPE_DISCOUNT);
    }

    public function typeIndex(string $type): View
    {
        $this->authorize('viewAny', Card::class);

        return $this->repo->employeeIndex(
            Card::resolveTypeOrFail($type)
        );
    }

    public function store(SaveCardRequest $request): JsonResponse
    {
        $this->authorize('create', Card::class);

        $validated = $request->validated();
        $result = $this->repo->store(
            $validated,
            user: $request->user(),
            includeData: false,
        );

        $organizationName = $request->user()?->tenant?->display_name
            ?? (function_exists('tenant') ? tenant()?->display_name : null)
            ?? 'Shop';

        return response()->json([
            'message' => $result['message'],
            'card_type' => $validated['card_type'],
            'html' => $this->repo->renderEmployeeCardHtml($result['card'], $organizationName),
        ], 201);
    }
}
