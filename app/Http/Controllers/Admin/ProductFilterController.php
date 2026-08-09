<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductFilterGroup;
use App\Models\ProductFilterOption;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductFilterController extends Controller
{
    public function index(): View
    {
        $groups = ProductFilterGroup::query()
            ->with(['options' => fn ($query) => $query->withCount('products')->orderBy('sort_order')->orderBy('name')])
            ->withCount('options')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.product-filters.index', compact('groups'));
    }

    public function storeGroup(Request $request): RedirectResponse
    {
        ProductFilterGroup::create($this->validateGroup($request));

        return back()->with('success', 'Grupa filtrów została dodana.');
    }

    public function updateGroup(Request $request, ProductFilterGroup $group): RedirectResponse
    {
        $group->update($this->validateGroup($request, $group));

        return back()->with('success', 'Grupa filtrów została zaktualizowana.');
    }

    public function destroyGroup(ProductFilterGroup $group): RedirectResponse
    {
        $group->delete();

        return back()->with('success', 'Grupa filtrów została usunięta.');
    }

    public function storeOption(Request $request, ProductFilterGroup $group): RedirectResponse
    {
        $group->options()->create($this->validateOption($request, $group));

        return back()->with('success', 'Opcja filtra została dodana.');
    }

    public function updateOption(Request $request, ProductFilterGroup $group, ProductFilterOption $option): RedirectResponse
    {
        abort_unless($option->product_filter_group_id === $group->id, 404);

        $option->update($this->validateOption($request, $group, $option));

        return back()->with('success', 'Opcja filtra została zaktualizowana.');
    }

    public function destroyOption(ProductFilterGroup $group, ProductFilterOption $option): RedirectResponse
    {
        abort_unless($option->product_filter_group_id === $group->id, 404);

        $option->delete();

        return back()->with('success', 'Opcja filtra została usunięta.');
    }

    private function validateGroup(Request $request, ?ProductFilterGroup $group = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:140', Rule::unique('product_filter_groups', 'slug')->ignore($group)],
            'description' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);
    }

    private function validateOption(Request $request, ProductFilterGroup $group, ?ProductFilterOption $option = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => [
                'nullable',
                'string',
                'max:140',
                Rule::unique('product_filter_options', 'slug')
                    ->where('product_filter_group_id', $group->id)
                    ->ignore($option),
            ],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);
    }
}
