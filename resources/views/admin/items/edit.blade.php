@extends('layouts.app')

@section('content')
<div class="container py-5">

    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">

            <div class="card shadow-lg mb-4">
                {{-- Header Sesuai Referensi --}}
                <div class="card-header border-bottom py-4">
                    <h4 class="mb-0 fw-bold fs-4">{{ __('Edit Item') }}</h4>
                </div>

                <div class="card-body p-4">

                    @include('partials.errors')
                    @include('partials.flash')

                    <form method="POST" action="{{ route('admin.items.update', $item) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        {{-- Section 1: Name & Unit --}}
                        <div class="row g-3 mb-3">
                            <div class="col-md-8">
                                <label class="form-label fw-medium">{{ __('Item Name') }}</label>
                                <input type="text" name="name" value="{{ old('name', $item->name) }}"
                                    class="form-control" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-medium">{{ __('Base Unit') }}</label>
                                {{-- Read-only input dengan background abu --}}
                                <input type="text" value="{{ $item->base_unit }}" class="form-control bg-body-tertiary"
                                    disabled>
                                <div class="form-text text-muted small">{{ __('Cannot be changed.') }}</div>
                            </div>
                        </div>

                        {{-- Section 2: Pricing & Stock --}}
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-medium">{{ __('Cost Price') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-body-tertiary">Rp</span>
                                    <input type="number" name="cost_price"
                                        value="{{ old('cost_price', $item->cost_price) }}" class="form-control"
                                        step="0.01" min="0" placeholder="0">
                                </div>
                                <div class="form-text text-muted small">{{ __('Cost per base unit.') }}</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-medium">{{ __('Low Stock Threshold') }}</label>
                                <input type="number" name="low_stock_threshold"
                                    value="{{ (float) old('low_stock_threshold', $item->low_stock_threshold) }}"
                                    class="form-control" step="0.001" min="0">
                                <div class="form-text text-muted small">{{ __('Alert when stock falls below this.') }}
                                </div>
                            </div>
                        </div>

                        {{-- Section 3: Image Upload (Improved UI) --}}
                        <div class="mb-4">
                            <label class="form-label fw-medium">{{ __('Item Image') }}</label>
                            <div class="card shadow-sm border border-secondary-subtle bg-body-tertiary">
                                <div class="card-body p-3 text-start">
                                    <input type="file" name="image" accept="image/*" class="form-control mb-3">

                                    @if($item->image_path)
                                    <div class="p-2 border rounded d-inline-block bg-body">
                                        <div class="small text-muted mb-1 text-center">{{ __('Current Image') }}</div>
                                        <img src="{{ Storage::url($item->image_path) }}"
                                            class="rounded object-fit-cover" style="height: 100px; width: 100px;"
                                            alt="{{ $item->name }}">
                                    </div>
                                    @else
                                    <div class="small text-muted fst-italic">
                                        {{ __('No image uploaded yet.') }}
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Status --}}
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                id="isActiveCheck" @checked(old('is_active', $item->is_active))>
                            <label class="form-check-label fw-medium" for="isActiveCheck">
                                {{ __('Active') }}
                            </label>
                            <div class="form-text small">{{ __('Inactive items will be hidden from selection.') }}</div>
                        </div>

                        <hr class="my-4">

                        {{-- Actions --}}
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.items.index') }}" class="btn btn-secondary fw-bold">
                                {{ __('Back') }}
                            </a>
                            <button type="submit" class="btn btn-primary fw-bold">
                                {{ __('Update') }}
                            </button>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection