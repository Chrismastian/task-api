<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tasks = $request->user()->tasks()
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->priority, fn ($q, $p) => $q->where('priority', $p))
            ->when($request->search, fn ($q, $s) => $q->where('title', 'like', "%{$s}%"))
            ->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END")
            ->orderBy('due_date')
            ->latest()
            ->get();

        return response()->json($tasks);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);

        $task = $request->user()->tasks()->create($data);

        return response()->json($task, 201);
    }

    public function show(Request $request, Task $task): JsonResponse
    {
        $this->authorizeTask($request, $task);

        return response()->json($task);
    }

    public function update(Request $request, Task $task): JsonResponse
    {
        $this->authorizeTask($request, $task);

        $task->update($this->validated($request, partial: true));

        return response()->json($task);
    }

    public function destroy(Request $request, Task $task): JsonResponse
    {
        $this->authorizeTask($request, $task);

        $task->delete();

        return response()->json(['message' => 'Task deleted']);
    }

    /**
     * Ownership check: a task from another user must look like a 404, not a 403,
     * so the API never confirms that someone else's task id exists.
     */
    private function authorizeTask(Request $request, Task $task): void
    {
        abort_unless($task->user_id === $request->user()->id, 404);
    }

    private function validated(Request $request, bool $partial = false): array
    {
        $required = $partial ? 'sometimes' : 'required';

        return $request->validate([
            'title' => [$required, 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', 'in:todo,in_progress,done'],
            'priority' => ['sometimes', 'in:low,medium,high'],
            'due_date' => ['nullable', 'date'],
        ]);
    }
}
