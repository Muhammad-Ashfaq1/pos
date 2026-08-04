{{-- Shared employee create-card modals (Discounts page + POS checkout). Requires $products, $currencySymbol. --}}
@php
    $currencySymbol = $currencySymbol ?? \App\Support\Currency::symbol();
    $products = $products ?? collect();
@endphp

@can('create', \App\Models\Card::class)
    <div
        class="modal fade"
        id="addDiscountCardModal"
        tabindex="-1"
        aria-labelledby="addDiscountCardModalLabel"
        aria-hidden="true"
        data-bs-backdrop="static"
        data-bs-keyboard="false"
    >
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form
                    method="POST"
                    action="{{ route('employee.cards.store') }}"
                    class="js-employee-card-form"
                    data-card-type="discount"
                    novalidate
                >
                    @csrf
                    <input type="hidden" name="card_type" value="discount">

                    <div class="modal-header">
                        <h5 class="modal-title" id="addDiscountCardModalLabel">Add Discount Card</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <x-cards.form-fields
                            card-type="discount"
                            :products="$products"
                            id-prefix="employee_discount"
                            modal-id="addDiscountCardModal"
                            :currency-symbol="$currencySymbol"
                            name-column="col-12"
                            :valid-until-required="true"
                        />
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-primary" type="submit" data-card-submit>Save Card</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @foreach (collect(\App\Models\Card::typeMeta())->except(\App\Models\Card::TYPE_DISCOUNT) as $module => $config)
        <div
            class="modal fade"
            id="{{ $config['modal'] }}"
            tabindex="-1"
            aria-labelledby="{{ $config['modal'] }}Label"
            aria-hidden="true"
            data-bs-backdrop="static"
            data-bs-keyboard="false"
        >
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <form
                        method="POST"
                        action="{{ route('employee.cards.store') }}"
                        class="js-employee-card-form"
                        data-card-type="{{ $module }}"
                        novalidate
                    >
                        @csrf
                        <input type="hidden" name="card_type" value="{{ $module }}">

                        <div class="modal-header">
                            <h5 class="modal-title" id="{{ $config['modal'] }}Label">
                                Add {{ $config['singular'] }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            <x-cards.form-fields
                                :card-type="$module"
                                :products="$products"
                                :id-prefix="'employee_'.$module"
                                :modal-id="$config['modal']"
                                :currency-symbol="$currencySymbol"
                                :value-label="$config['value_label']"
                            />
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn btn-primary" type="submit" data-card-submit>
                                Save {{ $config['singular'] }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
@endcan
