<?php
namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\{Sale, SaleItem, Payment, Product, InventoryMovement};
use App\Support\Cart as CartStore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function show()
    {
        $cart = CartStore::get();
        if (empty($cart['lines'])) {
            return redirect()->route('employee.sales.catalog');
        }
        $totals = CartStore::totals($cart);
        return view('employee.sales.checkout', compact('cart', 'totals'));
    }

    public function store(Request $r)
    {
        $cart = CartStore::get();
        abort_if(empty($cart['lines']), 422, 'Cart empty');

        $data = $r->validate([
            'method' => 'required|string|in:cash,card,qris',
            'paid'   => 'required|numeric|min:0',
        ]);

        $totals = CartStore::totals($cart);
        $paid   = (float) $data['paid'];
        $change = max(0, $paid - $totals['total']);

        $sale = DB::transaction(function () use ($r, $cart, $totals, $paid, $change, $data) {
            $sale = Sale::create([
                'invoice_no' => 'INV-'.now()->format('Ymd').'-'.strtoupper(Str::random(5)),
                'user_id'    => $r->user()->id,
                'total'      => $totals['total'],
                'paid'       => $paid,
                'change'     => $change,
                'status'     => $paid >= $totals['total'] ? 'paid' : 'draft',
            ]);

            foreach ($cart['lines'] as $l) {
                SaleItem::create([
                    'sale_id'    => $sale->id,
                    'product_id' => $l['product_id'],
                    'qty'        => $l['qty'],
                    'price'      => $l['price'],
                    'total'      => $totals['total'],
                ]);

                $product = Product::with(['linkedItem', 'bomLines.item'])->findOrFail($l['product_id']);

                if ($product->type === 'simple' && $product->linkedItem) {
                    $needed = (float) ($product->per_sale_qty ?? 1) * (float) $l['qty'];

                    $product->linkedItem->decrement('current_qty', $needed);

                    InventoryMovement::create([
                        'item_id'        => $product->linkedItem->id,
                        'change_qty'     => -$needed,
                        'reason'         => 'sale',
                        'reference_type' => 'sale',
                        'reference_id'   => $sale->id,
                        'note'           => 'Sale '.$sale->invoice_no.' / '.$product->name,
                    ]);

                } else {
                    foreach ($product->bomLines as $line) {
                        $consume = (float) $line->qty * (float) $l['qty'];

                        $line->item->decrement('current_qty', $consume);

                        InventoryMovement::create([
                            'item_id'        => $line->item->id,
                            'change_qty'     => -$consume,
                            'reason'         => 'sale',
                            'reference_type' => 'sale',
                            'reference_id'   => $sale->id,
                            'note'           => 'Sale '.$sale->invoice_no.' / '.$product->name.' (BOM)',
                        ]);
                    }
                }
            }

            Payment::create([
                'sale_id' => $sale->id,
                'method'  => $data['method'],
                'amount'  => $paid,
                'notes'   => null,
            ]);

            CartStore::clear();
            return $sale;
        });

        return redirect()->route('employee.sales.invoice.show', $sale);
    }
}
