@extends('layouts.app')
@section('content')
<div class="container-xxl py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 fw-bold mb-0 text-body">{{ __('Checkout') }}</h1>
        <a href="{{ route('employee.sales.cart.show') }}" class="btn btn-secondary d-flex align-items-center gap-2">
            {{ __('Back to Cart') }}
        </a>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('Product') }}</th>
                                <th class="text-end">{{ __('Qty') }}</th>
                                <th class="text-end">{{ __('Price') }}</th>
                                <th class="text-end">{{ __('Total') }}</th>
                            </tr>
                        </thead>

                        <tbody class="table-group-divider">
                            @foreach($cart['lines'] as $l)
                            <tr>
                                <td>{{ $l['name'] }}</td>
                                <td class="text-end">{{ $l['qty'] }}</td>
                                <td class="text-end">Rp {{ number_format($l['price'],0,',','.') }}</td>
                                <td class="text-end">Rp
                                    {{ number_format(($l['price']*$l['qty'])-($l['discount']??0),0,',','.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between fs-5"><span>{{ __('Total') }}</span><strong>Rp
                            {{ number_format($totals['total'],0,',','.') }}</strong></div>

                    <form class="mt-3" action="{{ route('employee.sales.checkout.store') }}" method="post">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label">{{ __('Payment Method') }}</label>
                            <select name="method" class="form-select">
                                <option value="cash">{{ __('Cash') }}</option>
                                <option value="card">{{ __('Card') }}</option>
                                <option value="qris">{{ __('QRIS') }}</option>
                            </select>
                        </div>

                        <div class="mb-2">
                            <label class="form-label">{{ __('Amount Received') }}</label>
                            <input type="number" placeholder="{{ __('Enter amount received') }}" step="0.01" min="0" name="paid" class="form-control" required>
                        </div>
                        <button class="btn btn-success w-100">{{ __('Confirm & Pay') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection