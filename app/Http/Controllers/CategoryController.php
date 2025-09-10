<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        return view('pages.dashboard.category-index', compact('categories'));
    }
    public function getAllCategories()
    {
        $categories = Category::orderBy('name')->get();
        return response()->json(['status' => 'success', 'data' => $categories]);
    }

    public function create()
    {
        $categories = Category::all();
        return view(
            'pages.dashboard.category-page',
            compact('categories')
        );
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string | unique:categories,name']);
        Category::create($request->only('name'));
        return redirect()->route('categories.index')->with('success', 'Category created successfully.');
    }

    public function edit(Category $category)
    {
        $categories = Category::all();
        return view('pages.dashboard.category-edit', compact('category', 'categories'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate(['name' => 'required|string| unique:categories,name,' . $category->id]);
        $category->update($request->only('name'));
        return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return redirect()->route('categories.index')->with('success', 'Category deleted successfully.');
    }
}
