<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Cards\SaveCardRequest;
use App\Models\Card;
use App\Repositories\Interface\CardRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CardController extends Controller
{
    public function __construct(
        private readonly CardRepositoryInterface $repo
    ) {}

    public function index(): RedirectResponse
    {
        $this->authorize('viewAny', Card::class);

        return redirect()->route('tenant.ecommerce.cards.type', [
            'type' => Card::TYPE_DISCOUNT,
        ]);
    }

    public function typeIndex(string $type): View
    {
        $this->authorize('viewAny', Card::class);
        $cardType = $this->resolveCardType($type);

        return $this->repo->index($cardType);
    }

    public function listing(string $type, Request $request): JsonResponse
    {
        $this->authorize('viewAny', Card::class);
        $cardType = $this->resolveCardType($type);

        $validated = $request->validate([
            'draw' => ['nullable', 'integer'],
            'start' => ['nullable', 'integer', 'min:0'],
            'length' => ['nullable', 'integer', 'min:1', 'max:100'],
            'search.value' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['1', '0'])],
            'sort' => ['nullable', Rule::in(['latest', 'name', 'value_high_low', 'valid_until'])],
            'columns' => ['nullable', 'array'],
            'columns.*.data' => ['nullable', 'string'],
            'order' => ['nullable', 'array'],
            'order.*.column' => ['nullable', 'integer', 'min:0'],
            'order.*.dir' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);

        $validated['card_type'] = $cardType;

        return response()->json(
            $this->repo->getCardsListing($validated, $request->user())
        );
    }

    public function edit(string $type, Card $card, Request $request): JsonResponse
    {
        $this->authorize('update', $card);
        $this->assertCardMatchesType($card, $type);

        return response()->json([
            'data' => $this->repo->getCardFormData($card, $request->user()),
        ]);
    }

    public function save(string $type, SaveCardRequest $request): JsonResponse
    {
        $cardType = $this->resolveCardType($type);
        $validated = $request->validated();
        $validated['card_type'] = $cardType;

        $card = isset($validated['id'])
            ? Card::query()->findOrFail($validated['id'])
            : null;

        if ($card) {
            $this->authorize('update', $card);
            $this->assertCardMatchesType($card, $cardType);
        } else {
            $this->authorize('create', Card::class);
        }

        $result = $this->repo->store(
            Arr::except($validated, ['id']),
            $card,
            $request->user(),
        );

        return response()->json([
            'message' => $result['message'],
            'data' => $result['data'],
        ]);
    }

    public function destroy(string $type, Card $card): JsonResponse
    {
        $this->authorize('delete', $card);
        $this->assertCardMatchesType($card, $type);

        $result = $this->repo->destroy($card);

        return response()->json([
            'message' => $result['message'],
        ]);
    }

    private function resolveCardType(string $type): string
    {
        if (! array_key_exists($type, Card::typeOptions())) {
            throw new NotFoundHttpException;
        }

        return $type;
    }

    private function assertCardMatchesType(Card $card, string $type): void
    {
        $cardType = $this->resolveCardType($type);

        if ($card->card_type !== $cardType) {
            throw new NotFoundHttpException;
        }
    }
}
