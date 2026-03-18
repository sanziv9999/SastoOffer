<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDisplayTypeRequest;
use App\Http\Requests\UpdateDisplayTypeRequest;
use App\Models\DisplayType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DisplayTypeCrudController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));

        $displayTypes = DisplayType::query()
            ->when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('admin/DisplayTypes/Index', [
            'displayTypes' => $displayTypes,
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/DisplayTypes/Create');
    }

    public function store(StoreDisplayTypeRequest $request): RedirectResponse
    {
        DisplayType::create($request->validated());

        return redirect()->route('admin.display-types.index')->with('success', 'Display type created.');
    }

    public function edit(DisplayType $display_type): Response
    {
        return Inertia::render('admin/DisplayTypes/Edit', [
            'displayType' => $display_type,
        ]);
    }

    public function update(UpdateDisplayTypeRequest $request, DisplayType $display_type): RedirectResponse
    {
        $display_type->update($request->validated());

        return redirect()->route('admin.display-types.index')->with('success', 'Display type updated.');
    }

    public function destroy(DisplayType $display_type): RedirectResponse
    {
        // Prevent deleting if used by any offer display mapping.
        if ($display_type->dealOfferTypes()->exists()) {
            return back()->with('error', 'Cannot delete this display type because it is used by deals.');
        }

        $display_type->delete();

        return redirect()->route('admin.display-types.index')->with('success', 'Display type deleted.');
    }
}

