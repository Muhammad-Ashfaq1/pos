@php
    $balanceLabel = $balanceLabel ?? null;
    $balanceClass = $balanceClass ?? 'psc-balance';
    $inputId = $inputId ?? null;
    $inputName = $inputName ?? null;
    $inputMax = $inputMax ?? null;
    $inputDataBalance = $inputDataBalance ?? null;
    $inputDataDue = $inputDataDue ?? null;
    $maxButtonId = $maxButtonId ?? null;
    $clearButtonId = $clearButtonId ?? null;
@endphp

<div class="psc-card">
    <div class="psc-top">
        <div class="psc-left">
            <div class="psc-header">
                <div class="psc-icon" aria-hidden="true">
                    <i class="ti tabler-wallet"></i>
                </div>
                <div class="psc-meta">
                    <div class="psc-title">Store Credit</div>
                    <div class="psc-sub">Available Balance</div>
                    <div class="{{ $balanceClass }}">{{ $balanceLabel ?? '' }}</div>
                </div>
            </div>
        </div>

        <div class="psc-right">
            <div class="psc-controls">
                <div class="psc-field">
                    <span class="psc-currency">{{ \App\Support\Currency::symbol() }}</span>
                    <input
                        type="number"
                        @if ($inputId) id="{{ $inputId }}" @endif
                        @if ($inputName) name="{{ $inputName }}" @endif
                        class="psc-input payment-store-credit-input"
                        min="0"
                        step="0.01"
                        @if ($inputMax !== null) max="{{ $inputMax }}" @endif
                        @if ($inputDataBalance !== null) data-balance="{{ $inputDataBalance }}" @endif
                        @if ($inputDataDue !== null) data-due="{{ $inputDataDue }}" @endif
                        value="0.00"
                        placeholder="0.00">
                </div>
                <button
                    type="button"
                    class="psc-btn psc-btn--max payment-store-credit-max"
                    @if ($maxButtonId) id="{{ $maxButtonId }}" @endif>
                    Use Max
                </button>
                <button
                    type="button"
                    class="psc-btn psc-btn--clear payment-store-credit-clear"
                    @if ($clearButtonId) id="{{ $clearButtonId }}" @endif>
                    Clear
                </button>
            </div>

            <div class="psc-banner payment-store-credit-status psc-banner--unlocked">
                <i class="ti tabler-circle-check payment-store-credit-status-icon"></i>
                <span class="payment-store-credit-status-text">Store credit unlocked and available to use.</span>
            </div>
        </div>
    </div>
</div>
