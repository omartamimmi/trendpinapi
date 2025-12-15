<div class="panel">
    <div class="panel-title"><strong>{{ __('shop::word.Discount') }}</strong></div>
    <div class="panel-body">
        <div class="form-group mt-2">
            <label>
                <input class="mb-3" type="checkbox"
                    name="enable_discount"
                    @isset($shop)

                @if (!empty($shop->meta->enable_discount) == 1)
                checked
                @endif @endisset
                    value="1"> {{ __('shop::word.Enable discount') }}
            </label>
        </div>
        <?php $old = old('discount', $shop->meta->discount ?? []); ?>
        <div class="row">
            <div class="col-md-6">
                <label>{{ __('shop::word.Discount type') }}</label>
                <select name="discount_type" id="discount_type"
                    class="form-select  @error('discount_type') is-invalid @enderror">
                    <option value="">{{ __('shop::word.please select a discount type') }}</option>


                    <option value="percentage">{{ __('shop::word.percentage') }}</option>
                    <option value="items">{{ __('shop::word.On items') }}</option>
                    <option value="other">{{ __('shop::word.Other') }}</option>

                </select>
            </div>
            <div class="col-md-6  discount-percentage-element d-none">
                <label>{{ __('shop::word.Discount percentage') }}</label>
                <input type="number" name="discount_percentage" class="form-control discount-type"
                    value="{{ old('discount', $shop->meta->discount ?? '') }}">
            </div>
            <div>
                <div class="col-md-6 discount-desc-element d-none">
                    <label>{{ __('shop::word.Discount description') }}</label>
                    <input type="text" name="discount_description" class="form-control">
                </div>
                <div class="col-md-6 discount-items-element d-none" id="discount-items-element">
                    <label>{{ __('shop::word.Discount on items') }}</label>
                    <select id="discount_type-item" name="discount_items"
                        class="form-select  @error('discount_type') is-invalid @enderror">
                        <option value="">{{ __('shop::word.please select a discount type') }}</option>
                        <option value="buy-one-get-one">{{ __('shop::word.Buy one get one free') }}</option>
                        <option value="buy-two-get-one">{{ __('shop::word.Buy two get one free') }}</option>
                        <option value="buy-three-get-one">{{ __('shop::word.Buy three get one free') }}</option>

                    </select>

                </div>

                <div class="col-6">
                    @error('discount_type')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
                </div>
            </div>

            <input type="hidden" name="discount">
        </div>
    </div>
</div>
<style>
    select {
        font-size: 14px !important;

    }

    .form-control {
        font-size: 14px;
        border-radius: 3px;
        padding: 5px 10px;
        border: 1px solid #c4cdd5;
        transition-property: background, border, box-shadow;
        transition-timing-function: cubic-bezier(0.64, 0, 0.35, 1);
        transition-duration: 200ms;
        box-shadow: inset 0 1px 0 0 rgb(63 63 68 / 5%);
        border-color: #c4cdd5;
        width: 100%;
    }
</style>
<script>
    $('#discount_type').on('change', function() {
        let disItem = ".discount-items-element";
        let disDes = '.discount-desc-element';
        let disPercentage = ".discount-percentage-element";
        let val = $(this).val();
        $(disPercentage).addClass('d-none')
        $(disDes).addClass('d-none')
        $(disItem).addClass('d-none')


        switch (val) {
            case 'percentage':
                $(disPercentage).removeClass('d-none')
                break;
            case 'items':
                $(disItem).removeClass('d-none')
                break;
            case 'other':
                $(disDes).removeClass('d-none')
                break;
        }

    })
</script>
