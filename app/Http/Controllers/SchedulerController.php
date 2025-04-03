<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SchedulerModel;

class SchedulerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return SchedulerModel::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store($scheduler)
    {
        SchedulerModel::create($scheduler);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        SchedulerModel::find($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        SchedulerModel::find($id)->update($request->all());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        SchedulerModel::find($id)->delete();
    }
}
