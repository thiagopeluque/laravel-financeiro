<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Auth::user()->categories()->orderBy('nome')->get();
        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        return view('categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'tipo' => 'required|in:receita,despesa'
        ]);

        Auth::user()->categories()->create($validated);

        return redirect()->route('categories.index')->with('success', 'Categoria criada com sucesso.');
    }

    public function edit(Category $category)
    {
        $this->authorizeUser($category);
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $this->authorizeUser($category);

        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'tipo' => 'required|in:receita,despesa'
        ]);

        $category->update($validated);

        return redirect()->route('categories.index')->with('success', 'Categoria atualizada com sucesso.');
    }

    public function destroy(Category $category)
    {
        $this->authorizeUser($category);
        $category->delete();
        return redirect()->route('categories.index')->with('success', 'Categoria excluída com sucesso.');
    }

    private function authorizeUser(Category $category)
    {
        if ($category->user_id !== Auth::id()) {
            abort(403);
        }
    }
}
