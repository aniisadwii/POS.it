@extends('layouts.app')

@section('content')
<div class="container-xxl py-4">
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1 text-body">
                Invoice #{{ $sale->invoice_no }}
                @php
                $badge = match($sale->status){
                'paid' => 'bg-success-subtle text-success-emphasis border border-success-subtle',
                'draft'=> 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                'void' => 'bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle',
                default=> 'bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle'
                };
                @endphp
                <span class="badge {{ $badge }} fs-6 ms-2 align-middle rounded-pill fw-normal">
                    {{ ucfirst($sale->status) }}
                </span>
            </h1>

            <p class="text-secondary mb-0">
                {{ $sale->created_at->format('d M Y, H:i') }} &bull; Cashier: {{ $sale->user->name ?? '-' }}
            </p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('employee.sales.history.index') }}" class="btn btn-secondary">Back</a>
            <button class="btn btn-primary" onclick="window.print()">Print</button>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th class="text-end">Qty</th>
                                <th class="text-end">Price</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>

                        <tbody class="table-group-divider">
                            @foreach($sale->items as $it)
                            <tr>
                                <td>{{ $it->product->name ?? '—' }}</td>
                                <td class="text-end">{{ $it->qty }}</td>
                                <td class="text-end">Rp {{ number_format($it->price,0,',','.') }}</td>
                                <td class="text-end">Rp {{ number_format($it->total,0,',','.') }}</td>
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
                    <div class="d-flex justify-content-between fs-5 mb-2">
                        <span>Total</span>
                        <strong>Rp {{ number_format($sale->total,0,',','.') }}</strong>
                    </div>

                    <div class="d-flex justify-content-between mt-2">
                        <span>Paid</span>
                        <span>Rp {{ number_format($sale->paid,0,',','.') }}</span>
                    </div>

                    <div class="d-flex justify-content-between">
                        <span>Change</span>
                        <span class="text-success fw-bold">{{ number_format($sale->change, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">Payments</div>
                <ul class="list-group list-group-flush">
                    @forelse($sale->payments as $p)
                    <li class="list-group-item d-flex justify-content-between">
                        <span>{{ strtoupper($p->method) }}</span>
                        <strong>Rp {{ number_format($p->amount,0,',','.') }}</strong>
                    </li>
                    @empty
                    <li class="list-group-item text-secondary">No payments.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection