<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CoursePlan;
use Illuminate\Http\Request;

class CoursePlanController extends Controller
{
    public function all()
    {
        $packages = CoursePlan::orderBy('level', 'asc')->get();
        return responseSuccess('packages', ['Packages retrieved successfully'], ['packages' => $packages]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            // Course plans are lifetime; keep field for UI but allow 0.
            'validity_days' => 'required|integer|min:0',
            'level' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'status' => 'required|in:0,1',
        ]);

        $plan = new CoursePlan();
        $plan->name = $request->name;
        $plan->slug = \Str::slug($request->name);
        $plan->price = $request->price;
        $plan->validity_days = 0;
        $plan->level = $request->level;
        $plan->description = $request->description;
        $plan->status = $request->status;
        $plan->save();

        return responseSuccess('package_created', ['Package created successfully'], ['package' => $plan]);
    }

    public function edit($id)
    {
        $plan = CoursePlan::findOrFail($id);
        return responseSuccess('package', ['Package retrieved successfully'], ['package' => $plan]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            // Course plans are lifetime; keep field for UI but allow 0.
            'validity_days' => 'required|integer|min:0',
            'level' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'status' => 'required|in:0,1',
        ]);

        $plan = CoursePlan::findOrFail($id);
        $plan->name = $request->name;
        $plan->slug = \Str::slug($request->name); 
        $plan->price = $request->price;
        $plan->validity_days = 0;
        $plan->level = $request->level;
        $plan->description = $request->description;
        $plan->status = $request->status;
        $plan->save();

        return responseSuccess('package_updated', ['Package updated successfully'], ['package' => $plan]);
    }

    public function delete($id)
    {
        $plan = CoursePlan::findOrFail($id);
        $plan->delete();
        return responseSuccess('package_deleted', ['Package deleted successfully']);
    }
}
