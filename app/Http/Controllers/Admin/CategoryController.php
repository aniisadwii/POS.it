<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index() {
        $cats = Category::orderBy('sort_order')->paginate(15);
        return view('admin/categories/index', compact('cats'));
    }

    public function create(){ 
        return view('admin/categories/create'); 
    }

    public function store(Request $request){
        $data = $request->validate([
            'name' => ['required','max:100','unique:categories,name'],
            'sort_order' => ['nullable','integer','min:0'],
            'is_active' => ['boolean'],
        ]);

        Category::create([
            'name'=>$data['name'],
            'slug'=>Str::slug($data['name']),
            'sort_order'=>$data['sort_order'] ?? 0,
            'is_active'=>(bool)($data['is_active'] ?? true),
        ]);

        return redirect()->route('admin.categories.index')->with('ok','Category created');
    }
    public function edit(Category $category){
        return view('admin/categories/edit', compact('category'));
    
    }
    public function update(Request $request, Category $category){
        $data = $request->validate([
            'name'=> 'required|max:100|unique:categories,name,'.$category->id,
            'sort_order'=>['nullable','integer','min:0'],
            'is_active'=>['boolean'],
        ]);

        $category->update([
            'name'=>$data['name'],
            'slug'=>Str::slug($data['name']),
            'sort_order'=>$data['sort_order'] ?? 0,
            'is_active'=>(bool)($data['is_active'] ?? true),
        ]);

        return back()->with('ok','Category updated');
    }
    public function destroy(Category $category) {
        $category->delete();
        return back()->with('ok','Category deleted');
    }
}
