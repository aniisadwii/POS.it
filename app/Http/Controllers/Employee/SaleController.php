<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\{Sale, SaleItem, Payment, Product, Item, InventoryMovement};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SaleController extends Controller
{
    protected function cart(Request $r){ return $r->session()->get('pos_cart', ['lines'=>[]]); }
    protected function saveCart(Request $r, $cart){ $r->session()->put('pos_cart', $cart); }

    public function index(){
        $sales = Sale::withCount('items')->latest()->paginate(20);
        return view('employee.sales.index', compact('sales'));
    }

    public function create(Request $r){
        $cart = $this->cart($r);
        $products = \App\Models\Product::orderBy('name')->get(['id','name','price','type']);
        return view('employee.sales.create', compact('cart','products'));
    }

    public function cartAdd(Request $r){
        $data = $r->validate([
            'product_id' => 'required|exists:products,id',
            'qty'        => 'required|numeric|min:0.001'
        ]);
        $p = Product::findOrFail($data['product_id']);

        $cart = $this->cart($r);
        $i = collect($cart['lines'])->search(fn($l)=>$l['product_id']==$p->id);

        if ($i!==false) $cart['lines'][$i]['qty'] += (float)$data['qty'];
        else {
            $cart['lines'][] = [
                'product_id'=>$p->id,
                'name'=>$p->name,
                'price'=>$p->price ?? 0,
                'qty'=>(float)$data['qty'],
                'type'=>$p->type,
            ];
        }

        $this->saveCart($r,$cart);
        return response()->json(['ok'=>true]);
    }

    public function cartRemove(Request $r){
        $data = $r->validate(['product_id'=>'required|exists:products,id']);
        $cart = $this->cart($r);
        $cart['lines'] = collect($cart['lines'])->reject(fn($l)=>$l['product_id']==$data['product_id'])->values()->all();
        $this->saveCart($r,$cart);
        return response()->json(['ok'=>true]);
    }

    public function store(Request $r){
        $cart = $this->cart($r);
        abort_if(empty($cart['lines']), 422, 'Cart empty');

        $total = collect($cart['lines'])->sum(fn($l) => $l['price'] * $l['qty']);

        $data = $r->validate([
            'paid'          => 'required|numeric|min:0',
            'payment_method'=> 'nullable|string' 
        ]);
        $paid   = (float)$data['paid'];
        $change = max(0, $paid - $total);

        $sale = DB::transaction(function() use ($r,$cart,$subtotal,$discount,$tax,$total,$paid,$change){
            $sale = Sale::create([
                'invoice_no' => 'INV-'.strtoupper(Str::random(8)),
                'user_id'    => $r->user()->id,
                'total'      => $total,
                'paid'       => $paid,
                'change'     => $change,
                'status'     => $paid >= $total ? 'paid' : 'draft',
            ]);

            foreach ($cart['lines'] as $l) {
                $lineTotal = ($l['price'] * $l['qty']); 
                SaleItem::create([
                    'sale_id'   => $sale->id,
                    'product_id'=> $l['product_id'],
                    'qty'       => $l['qty'],
                    'price'     => $l['price'],
                    'total'     => $lineTotal,
                ]);

                $product = Product::with(['item','bomComponents'])->find($l['product_id']);
                if ($product->type === 'simple') {
                    if ($product->item_id) {
                        $item = $product->item;
                        $consume = $l['qty']; 
                        $item->decrement('current_qty', $consume);

                        InventoryMovement::create([
                            'item_id' => $item->id,
                            'type'    => 'sale',
                            'qty'     => -$consume,
                            'note'    => 'Sale '.$sale->invoice_no.' / '.$product->name,
                            'user_id' => $r->user()->id,
                        ]);
                    }
                } else {
                    foreach ($product->bomComponents as $comp) {
                        $item = $comp;
                        $consume = $comp->pivot->qty * $l['qty'];
                        $item->decrement('current_qty', $consume);

                        InventoryMovement::create([
                            'item_id' => $item->id,
                            'type'    => 'sale',
                            'qty'     => -$consume,
                            'note'    => 'Sale '.$sale->invoice_no.' / '.$product->name.' (BOM)',
                            'user_id' => $r->user()->id,
                        ]);
                    }
                }
            }

            if ($paid > 0) {
                Payment::create([
                    'sale_id' => $sale->id,
                    'method'  => request('payment_method','cash'),
                    'amount'  => $paid,
                    'notes'   => null,
                ]);
            }

            $r->session()->forget('pos_cart');

            return $sale;
        });

        return redirect()->route('employee.sales.show', $sale)->with('ok','Sale recorded');
    }

    public function show(Sale $sale){
        $sale->load(['items.product','payments','user']);
        return view('employee.sales.show', compact('sale'));
    }
}
