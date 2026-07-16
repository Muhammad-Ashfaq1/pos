@extends('layouts.employee-portal')

@section('title', 'Cards')

@section('content')
<style>
    .cards-shell { display:grid; grid-template-columns:240px minmax(0,1fr); gap:1.5rem; }
    .cards-nav, .cards-content { background:var(--bs-body-bg); border:1px solid var(--bs-border-color); border-radius:16px; box-shadow:0 8px 24px rgba(var(--bs-primary-rgb),.06); }
    .cards-nav { padding:1rem; align-self:start; }
    .cards-nav-link { display:flex; align-items:center; gap:.75rem; width:100%; padding:.85rem 1rem; border:0; border-radius:10px; color:var(--bs-secondary-color); background:transparent; font-weight:600; text-align:left; }
    .cards-nav-link.active { color:#fff; background:var(--bs-primary); }
    .cards-nav-link:not(.active):hover { background:rgba(var(--bs-primary-rgb),.10); color:var(--bs-primary); }
    .cards-content { padding:1.5rem; min-height:480px; }
    .discount-card { overflow:hidden; border:0; border-radius:16px; color:#fff; background:linear-gradient(135deg,var(--bs-primary),color-mix(in srgb,var(--bs-primary) 68%,#fff)); box-shadow:0 10px 25px rgba(var(--bs-primary-rgb),.22); }
    .discount-card .discount-value { font-size:2rem; font-weight:800; }
    @media (max-width:767.98px) { .cards-shell { grid-template-columns:1fr; } }
</style>

<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <a href="{{ route('employee.dashboard') }}" class="text-muted small"><i class="ti tabler-arrow-left me-1"></i>Dashboard</a>
        <h2 class="mb-0 mt-1">Cards</h2>
        <p class="text-muted mb-0">Create and manage customer card offers.</p>
    </div>
</div>

<div class="cards-shell">
    <aside class="cards-nav">
        <button class="cards-nav-link active" type="button" data-card-section="discount">
            <i class="ti tabler-discount-2"></i> Discount Card
        </button>
        <button class="cards-nav-link" type="button" data-card-section="gift">
            <i class="ti tabler-gift-card"></i> Gift Card
        </button>
        <button class="cards-nav-link" type="button" data-card-section="reward">
            <i class="ti tabler-award"></i> Reward Card
        </button>
    </aside>

    <section class="cards-content">
        <div id="discountSection">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                <div>
                    <h4 class="mb-1">Discount Cards</h4>
                    <span class="text-muted">Offers available to your customers</span>
                </div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDiscountCardModal">
                    <i class="ti tabler-plus me-1"></i>Add Discount Card
                </button>
            </div>

            <div class="row g-4">
                @forelse($cardsByType->get('discount', collect()) as $card)
                    <div class="col-md-6 col-xl-4">
                        <article class="card discount-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-4">
                                    <i class="ti tabler-discount-2 fs-2"></i>
                                    <span class="badge bg-white text-primary">{{ ucfirst($card->discount_type) }}</span>
                                </div>
                                <h5 class="text-white mb-1">{{ $card->name }}</h5>
                                <div class="discount-value">
                                    {{ $card->discount_type === 'percentage' ? rtrim(rtrim($card->value, '0'), '.') . '%' : \App\Support\Currency::symbol() . number_format((float) $card->value, 2) }}
                                </div>
                                <div class="small opacity-75 mt-3">
                                    Min. spend: {{ \App\Support\Currency::symbol() }}{{ number_format((float) $card->minimum_spend, 2) }}<br>
                                    Product: {{ $card->product?->name ?? 'All products' }}<br>
                                    Valid until {{ $card->valid_until->format('M d, Y') }}
                                </div>
                            </div>
                        </article>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="ti tabler-cards fs-1 text-muted"></i>
                            <h5 class="mt-3">No discount cards yet</h5>
                            <p class="text-muted">Create your first discount card to see it here.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        @foreach([
            'gift' => ['title' => 'Gift Cards', 'singular' => 'Gift Card', 'icon' => 'tabler-gift-card'],
            'reward' => ['title' => 'Reward Cards', 'singular' => 'Reward Card', 'icon' => 'tabler-award'],
        ] as $module => $config)
            <div id="{{ $module }}Section" class="card-module d-none">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                    <div>
                        <h4 class="mb-1">{{ $config['title'] }}</h4>
                        <span class="text-muted">Manage {{ strtolower($config['title']) }} independently.</span>
                    </div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add{{ ucfirst($module) }}CardModal">
                        <i class="ti tabler-plus me-1"></i>Add {{ $config['singular'] }}
                    </button>
                </div>

                <div class="row g-4">
                    @forelse($cardsByType->get($module, collect()) as $card)
                        <div class="col-md-6 col-xl-4">
                            <article class="card discount-card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-4">
                                        <i class="ti {{ $config['icon'] }} fs-2"></i>
                                        <span class="badge bg-white text-primary">{{ $config['singular'] }}</span>
                                    </div>
                                    <h5 class="text-white mb-1">{{ $card->name }}</h5>
                                    <div class="discount-value">
                                        @if($module === 'gift')
                                            {{ \App\Support\Currency::symbol() }}{{ number_format((float) $card->value, 2) }}
                                        @else
                                            {{ number_format((float) $card->value) }} points
                                        @endif
                                    </div>
                                    <div class="small opacity-75 mt-3">
                                        Min. spend: {{ \App\Support\Currency::symbol() }}{{ number_format((float) $card->minimum_spend, 2) }}<br>
                                        Product: {{ $card->product?->name ?? 'All products' }}<br>
                                        {{ $card->valid_until ? 'Valid until '.$card->valid_until->format('M d, Y') : 'No expiry date' }}
                                    </div>
                                </div>
                            </article>
                        </div>
                    @empty
                        <div class="col-12 text-center py-5">
                            <i class="ti {{ $config['icon'] }} fs-1 text-muted"></i>
                            <h5 class="mt-3">No {{ strtolower($config['title']) }} yet</h5>
                            <p class="text-muted">Create your first {{ strtolower($config['singular']) }} to see it here.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </section>
</div>

<div class="modal fade" id="addDiscountCardModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('employee.cards.store') }}">
                @csrf
                <input type="hidden" name="card_type" value="discount">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title">Add Discount Card</h5>
                        <small class="text-muted">Define the offer and its eligibility.</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" for="cardName">Card Name</label>
                            <input class="form-control" id="cardName" name="name" value="{{ old('name') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="discountType">Discount Type</label>
                            <select class="form-select" id="discountType" name="discount_type" required>
                                <option value="percentage" @selected(old('discount_type') === 'percentage')>Percentage</option>
                                <option value="fixed" @selected(old('discount_type') === 'fixed')>Fixed Amount</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="discountValue" id="discountValueLabel">Discount Percentage</label>
                            <div class="input-group">
                                <span class="input-group-text" id="discountValuePrefix">%</span>
                                <input type="number" class="form-control" id="discountValue" name="value" value="{{ old('value') }}" min="0.01" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="minimumSpend">Minimum Spend Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">{{ \App\Support\Currency::symbol() }}</span>
                                <input type="number" class="form-control" id="minimumSpend" name="minimum_spend" value="{{ old('minimum_spend', 0) }}" min="0" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="productId">Select Product</label>
                            <select class="form-select" id="productId" name="product_id">
                                <option value="">All products</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" @selected((string) old('product_id') === (string) $product->id)>{{ $product->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="validUntil">Valid Until</label>
                            <input type="date" class="form-control" id="validUntil" name="valid_until" value="{{ old('valid_until') }}" min="{{ now()->toDateString() }}" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" type="submit">Save Card</button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach([
    'gift' => ['title' => 'Gift Card', 'valueLabel' => 'Gift Amount', 'prefix' => \App\Support\Currency::symbol(), 'step' => '0.01'],
    'reward' => ['title' => 'Reward Card', 'valueLabel' => 'Reward Points', 'prefix' => 'PTS', 'step' => '1'],
] as $module => $config)
    <div class="modal fade" id="add{{ ucfirst($module) }}CardModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('employee.cards.store') }}">
                    @csrf
                    <input type="hidden" name="card_type" value="{{ $module }}">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title">Add {{ $config['title'] }}</h5>
                            <small class="text-muted">Saved in the {{ $module }} card module.</small>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Card Name</label>
                                <input class="form-control" name="name" value="{{ old('card_type') === $module ? old('name') : '' }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ $config['valueLabel'] }}</label>
                                <div class="input-group">
                                    <span class="input-group-text">{{ $config['prefix'] }}</span>
                                    <input type="number" class="form-control" name="value" value="{{ old('card_type') === $module ? old('value') : '' }}" min="0.01" step="{{ $config['step'] }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Minimum Spend Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">{{ \App\Support\Currency::symbol() }}</span>
                                    <input type="number" class="form-control" name="minimum_spend" value="{{ old('card_type') === $module ? old('minimum_spend', 0) : 0 }}" min="0" step="0.01" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Select Product</label>
                                <select class="form-select" name="product_id">
                                    <option value="">All products</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" @selected(old('card_type') === $module && (string) old('product_id') === (string) $product->id)>{{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Valid Until</label>
                                <input type="date" class="form-control" name="valid_until" value="{{ old('card_type') === $module ? old('valid_until') : '' }}" min="{{ now()->toDateString() }}">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-primary" type="submit">Save {{ $config['title'] }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach
@endsection

@push('page-script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const type = document.getElementById('discountType');
    const label = document.getElementById('discountValueLabel');
    const prefix = document.getElementById('discountValuePrefix');
    const value = document.getElementById('discountValue');

    function updateDiscountField() {
        const percentage = type.value === 'percentage';
        label.textContent = percentage ? 'Discount Percentage' : 'Fixed Amount';
        prefix.textContent = percentage ? '%' : @json(\App\Support\Currency::symbol());
        value.max = percentage ? '100' : '';
    }
    type.addEventListener('change', updateDiscountField);
    updateDiscountField();

    function activateModule(module) {
        document.querySelectorAll('[data-card-section]').forEach(function (item) {
            item.classList.toggle('active', item.dataset.cardSection === module);
        });
        ['discount', 'gift', 'reward'].forEach(function (name) {
            document.getElementById(name + 'Section').classList.toggle('d-none', name !== module);
        });
    }

    document.querySelectorAll('[data-card-section]').forEach(function (button) {
        button.addEventListener('click', function () {
            activateModule(button.dataset.cardSection);
            const url = new URL(window.location.href);
            url.searchParams.set('module', button.dataset.cardSection);
            window.history.replaceState({}, '', url);
        });
    });

    const requestedModule = new URL(window.location.href).searchParams.get('module');
    const initialModule = ['discount', 'gift', 'reward'].includes(requestedModule)
        ? requestedModule
        : @json(old('card_type', 'discount'));
    activateModule(initialModule);

    @if($errors->any())
        const errorType = @json(old('card_type', 'discount'));
        const modalId = errorType === 'discount'
            ? 'addDiscountCardModal'
            : 'add' + errorType.charAt(0).toUpperCase() + errorType.slice(1) + 'CardModal';
        bootstrap.Modal.getOrCreateInstance(document.getElementById(modalId)).show();
    @endif
});
</script>
@endpush
