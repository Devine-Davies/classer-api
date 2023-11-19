<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SchedulerJob;

class SchedulerJobController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return SchedulerJob::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store($request) //SchedulerJob
    {
        SchedulerJob::create($request);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        SchedulerJob::find($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        SchedulerJob::find($id)->update($request->all());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        SchedulerJob::find($id)->delete();
    }
}
