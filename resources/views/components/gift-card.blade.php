@props([
    'amount' => 0,
    'status' => 'unpaid',
    'statusLabel' => null,
    'cardLabel' => '',
    'uniqueId' => '',
    'expiresAt' => null,
    'organizationName' => null,
    'showPayNow' => true,
])

@php
    $orgName = $organizationName
        ?? auth()->user()?->tenant?->display_name
        ?? (function_exists('tenant') ? tenant()?->display_name : null)
        ?? 'Shop';

    $words = preg_split('/\s+/', trim($orgName), -1, PREG_SPLIT_NO_EMPTY);
    if (count($words) > 1) {
        $orgPrimary = $words[0];
        $orgAccent  = implode('', array_slice($words, 1));
    } else {
        $len     = mb_strlen($orgName);
        $splitAt = max(1, (int) floor($len / 2));
        $orgPrimary = mb_substr($orgName, 0, $splitAt);
        $orgAccent  = mb_substr($orgName, $splitAt);
    }

    $isPaid     = in_array(strtolower((string) $status), ['paid', 'redeemed', 'active'], true);
    $badgeLabel = $statusLabel ?? ($isPaid ? 'Paid' : 'Not Paid');
    $badgeClass = $isPaid ? 'gift-card__badge--paid' : 'gift-card__badge--unpaid';

    $currencySymbol = \App\Support\Currency::symbol();
    $numericAmount  = (float) $amount;
    $amountValue    = fmod($numericAmount, 1.0) === 0.0
        ? number_format($numericAmount, 0)
        : number_format($numericAmount, 2);

    $displayId = is_numeric($uniqueId)
        ? str_pad((string) (int) $uniqueId, 6, '0', STR_PAD_LEFT)
        : (string) $uniqueId;

    $expiryText = $expiresAt instanceof \Illuminate\Support\Carbon
        ? $expiresAt->format('d/m/Y')
        : ($expiresAt ? \Illuminate\Support\Carbon::parse($expiresAt)->format('d/m/Y') : '—');

    $bgUrl = asset('assets/img/gift-card-bg.png') . '?v=' . filemtime(public_path('assets/img/gift-card-bg.png'));
@endphp

<article
    {{ $attributes->class(['gift-card'])->merge([
        'style' => '--gc-bg-image: url(\'' . $bgUrl . '\')',
    ]) }}
>
    <div class="gift-card__inner">

        {{-- "Gift CARD" — absolute overlay, sits behind other content --}}
        <div class="gift-card__center" aria-hidden="true">
            <div class="gift-card__title-script">Gift</div>
            <div class="gift-card__title-caps">CARD</div>
        </div>

        {{-- Row 1: badges at top corners --}}
        <div class="gift-card__row gift-card__row--top">
            <span class="gift-card__badge {{ $badgeClass }}">{{ $badgeLabel }}</span>
            @if (! $isPaid && $showPayNow)
                <span class="gift-card__badge gift-card__badge--pay">Pay Now</span>
            @else
                <span class="gift-card__badge-spacer"></span>
            @endif
        </div>

        {{-- Body: LEFT col (org, label, spacer, date) | RIGHT col (amount, spacer, ID) --}}
        <div class="gift-card__body">

            {{-- LEFT: org (top) → spacer → label (middle) → date (bottom) --}}
            <div class="gift-card__col gift-card__col--left">
                <div class="gift-card__org" aria-label="{{ $orgName }}">
                    <span class="gift-card__org-primary">{{ $orgPrimary }}</span><span class="gift-card__org-accent">{{ $orgAccent }}</span>
                </div>
                <div class="gift-card__col-grow"></div>
                @if ($cardLabel !== '')
                    <div class="gift-card__label">{{ $cardLabel }}</div>
                @endif
                <div class="gift-card__expiry">{{ $expiryText }}</div>
            </div>

            {{-- RIGHT: amount (top) → spacer → ID box (bottom) --}}
            <div class="gift-card__col gift-card__col--right">
                <div class="gift-card__amount">
                    <span class="gift-card__amount-symbol">{{ $currencySymbol }}</span><span class="gift-card__amount-value">{{ $amountValue }}</span>
                </div>
                <div class="gift-card__col-grow"></div>
                @if ($displayId !== '')
                    <div class="gift-card__id-box">{{ $displayId }}</div>
                @endif
            </div>

        </div>

    </div>
</article>
