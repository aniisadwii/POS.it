@extends('layouts.app')

@section('content')
<div class="container py-5">
    
    <div class="row justify-content-center">
        
        <div class="col-md-10 col-lg-7">
            
            <div class="card shadow-lg">
                <div class="card-header d-flex justify-content-between align-items-center py-3 mt-2">
                    <h4 class="mb-2 fw-bold fs-4">{{ __('Edit BOM — :product', ['product' => $product->name]) }}</h4>
                </div>

                <div class="card-body">
                    
                    @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                        </ul>
                    </div>
                    @endif

                    @if (session('ok'))
                    <div class="alert alert-success">{{ session('ok') }}</div>
                    @endif

                    @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <form method="POST" action="{{ route('admin.products.bom.update', $product) }}">
                        @csrf @method('PUT')

                        <div id="bom-rows">
                            @php $rows = old('bom.item_id', $product->bomLines->pluck('item_id')->toArray()); @endphp
                            @php $qtys = old('bom.qty', $product->bomLines->pluck('qty')->toArray()); @endphp

                            @if(empty($rows))
                                @php $rows = ['']; $qtys = ['']; @endphp
                            @endif

                            @foreach($rows as $i => $iid)
                            <div class="row g-2 align-items-end mb-3 bom-item-row">
                                <div class="col-md-6">
                                    <label class="form-label small text-muted">{{ __('Select Item') }}</label>
                                    <select name="bom[item_id][]" class="form-select" required>
                                        <option value="">— {{ __('Select Item') }} —</option>
                                        @foreach($items as $it)
                                        <option value="{{ $it->id }}" @selected($iid==$it->id)>
                                            {{ $it->name }} ({{ $it->base_unit }})
                                        </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label small text-muted">{{ __('Qty') }}</label>
                                    <input name="bom[qty][]" type="number" step="0.001" min="0.001"
                                        @php
                                            $val = is_array($qtys) ? ($qtys[$i] ?? '') : $qtys;
                                        @endphp
                                        value="{{ is_numeric($val) ? (float) $val : $val }}" 
                                        class="form-control"
                                        placeholder="{{ __('Qty') }}" required>
                                </div>

                                <div class="col-md-3">
                                    <button type="button" class="btn btn-outline-danger w-100 remove-row">
                                        {{ __('Remove') }}
                                    </button>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="mb-4">
                            <button type="button" id="add-row" class="btn btn-outline-secondary btn-sm py-2">
                                {{ __('+ Add Row') }}
                            </button>
                        </div>

                        <div class="alert alert-secondary d-inline-flex align-items-center gap-4 mb-3" role="alert">
                            <div>
                                <strong>{{ __('Estimated Cost:') }}</strong> 
                                Rp {{ number_format($estimatedCost,2,',','.') }}
                            </div>
                            <div class="small opacity-75 border-start border-secondary ps-3">
                                {{ __('*Based on cost_price') }}
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.products.show',$product) }}" class="btn btn-secondary fw-bold">
                                {{ __('Back') }}
                            </a>
                            <button class="btn btn-primary fw-bold">
                                {{ __('Save BOM') }}
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        
        </div> 
    </div> 
</div>

<script>
document.getElementById('add-row').addEventListener('click', () => {
    const container = document.getElementById('bom-rows');
    // Template disesuaikan dengan GRID baru (6 - 3 - 3)
    const tpl = `
    <div class="row g-2 align-items-end mb-3 bom-item-row">
      <div class="col-md-6">
        <select name="bom[item_id][]" class="form-select" required>
          <option value="">— {{ __('Select item') }} —</option>
          @foreach($items as $it)
            <option value="{{ $it->id }}">{{ $it->name }} ({{ $it->base_unit }})</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-3">
        <input name="bom[qty][]" type="number" step="0.001" min="0.001" class="form-control" placeholder="{{ __('Qty') }}" required>
      </div>
      <div class="col-md-3">
        <button type="button" class="btn btn-outline-danger w-100 remove-row">{{ __('Remove') }}</button>
      </div>
    </div>`;
    container.insertAdjacentHTML('beforeend', tpl);
});

document.addEventListener('click', (e) => {
    if (e.target.classList.contains('remove-row')) {
        e.target.closest('.bom-item-row').remove();
    }
});
</script>
@endsection