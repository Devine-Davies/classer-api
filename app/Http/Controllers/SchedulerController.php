<?php

namespace App\Http\Controllers;

use App\Models\SchedulerModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class SchedulerController extends Controller
{
    /**
     * Fields accepted for create/update. Mirrors SchedulerModel::$fillable.
     */
    private const VALIDATION_RULES = [
        'command' => 'required|string|max:255',
        'metadata' => 'nullable|array',
        'scheduled_for' => 'nullable|date',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(): Collection
    {
        return SchedulerModel::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate(self::VALIDATION_RULES);

        $entity = SchedulerModel::create($data);

        return response()->json($entity, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $entity = SchedulerModel::findOrFail($id);

        return response()->json($entity);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $data = $request->validate(self::VALIDATION_RULES);

        $entity = SchedulerModel::findOrFail($id);
        $entity->update($data);

        return response()->json($entity);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $entity = SchedulerModel::findOrFail($id);
        $entity->delete();

        return response()->json(null, 204);
    }
}
