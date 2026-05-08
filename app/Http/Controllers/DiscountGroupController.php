<?php
namespace App\Http\Controllers;

use App\Models\DiscountGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DiscountGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('tenant.ecommerce.discounts.group.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $discountGroups = DiscountGroup::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request data
        $validated = $request->validate([
            'title'     => 'required|string|max:255',
            'type'      => 'required|in:percentage,fixed',
            'value'     => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['name'] = $validated['title'] ?? null;
        $validated['slug'] = Str::slug($validated['title'] ?? '');

        DiscountGroup::create($validated);

        return redirect()->route('tenant.discounts.group.index')->with('success', 'Discount group created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(DiscountGroup $discountGroup)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DiscountGroup $discountGroup)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DiscountGroup $discountGroup)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DiscountGroup $discountGroup)
    {
        //
    }
}
