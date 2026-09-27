<?php

namespace App\Http\Controllers;

use App\Models\Designation;
use App\Http\Requests\StoreDesignationRequest;
use App\Http\Requests\UpdateDesignationRequest;
use Illuminate\Http\Request;

class DesignationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $designations = Designation::withCount('employees')->get();
        return view('admin.designation.index', compact('designations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.designation.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDesignationRequest $request)
    {
        Designation::create($request->all());
        return back()->with('success', 'Designation created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $designation = Designation::findOrFail($id);
        return view('admin.designation.show', compact('designation'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $designation = Designation::findOrFail($id);
        return view('admin.designation.edit', compact('designation'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDesignationRequest $request, $id)
    {
        $designation = $id instanceof Designation && $id->exists ? $id : Designation::findOrFail($request->route('id') ?? (is_numeric($id) ? $id : $id->id));
        $designation->update($request->all());
        return back()->with('success', 'Designation updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $designation = $id instanceof Designation && $id->exists ? $id : Designation::findOrFail($id);
        if ($designation->employees()->count() > 0) {
            return back()->with('error', 'Cannot delete designation with active employees.');
        }
        $designation->delete();
        return back()->with('success', 'Designation deleted successfully!');
    }
}
