<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateTaskRequest;
use App\Http\Requests\UpdateTaskStatusRequest;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    /**
     * POST /api/tasks
     * Create a new task.
     */
    public function store(CreateTaskRequest $request): JsonResponse
    {
        $task = Task::create([
            'title'    => $request->title,
            'due_date' => $request->due_date,
            'priority' => $request->priority,
            'status'   => 'pending',
        ]);

        return response()->json([
            'message' => 'Task created successfully.',
            'data'    => $task,
        ], 201);
    }

    /**
     * GET /api/tasks
     * List all tasks, sorted by priority (high→low) then due_date asc.
     * Optional ?status= filter.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'status' => 'nullable|in:pending,in_progress,done',
        ]);

        $tasks = Task::query()
            ->byStatus($request->query('status'))
            ->sortedByPriority()
            ->get();

        if ($tasks->isEmpty()) {
            return response()->json([
                'message' => 'No tasks found.',
                'data'    => [],
            ], 200);
        }

        return response()->json([
            'message' => 'Tasks retrieved successfully.',
            'data'    => $tasks,
            'total'   => $tasks->count(),
        ], 200);
    }

    /**
     * PATCH /api/tasks/{id}/status
     * Update task status (can only progress: pending → in_progress → done).
     */
    public function updateStatus(UpdateTaskStatusRequest $request, int $id): JsonResponse
    {
        $task = Task::findOrFail($id);

        $newStatus = $request->status;

        // Validate status transition
        if (! $task->canTransitionTo($newStatus)) {
            $validNext = $task->nextStatus();
            $message = $validNext
                ? "Invalid status transition. From '{$task->status}', you can only move to '{$validNext}'."
                : "Task is already '{$task->status}' (final status) and cannot be updated further.";

            return response()->json([
                'message' => $message,
                'current_status' => $task->status,
                'valid_next_status' => $validNext,
            ], 422);
        }

        $task->update(['status' => $newStatus]);

        return response()->json([
            'message' => 'Task status updated successfully.',
            'data'    => $task->fresh(),
        ], 200);
    }

    /**
     * DELETE /api/tasks/{id}
     * Delete a task (only if status is 'done').
     */
    public function destroy(int $id): JsonResponse
    {
        $task = Task::findOrFail($id);

        if ($task->status !== 'done') {
            return response()->json([
                'message' => 'Forbidden. Only tasks with status "done" can be deleted.',
                'current_status' => $task->status,
            ], 403);
        }

        $task->delete();

        return response()->json([
            'message' => 'Task deleted successfully.',
        ], 200);
    }

    /**
     * GET /api/tasks/report?date=YYYY-MM-DD
     * Bonus: Daily task report grouped by priority and status.
     */
    public function report(Request $request): JsonResponse
    {
        $request->validate([
            'date' => 'required|date|date_format:Y-m-d',
        ]);

        $date = $request->query('date');

        $priorities = ['high', 'medium', 'low'];
        $statuses   = ['pending', 'in_progress', 'done'];

        // Get counts grouped by priority and status for the given due_date
        $results = Task::query()
            ->select('priority', 'status', DB::raw('COUNT(*) as count'))
            ->whereDate('due_date', $date)
            ->groupBy('priority', 'status')
            ->get()
            ->groupBy('priority');

        // Build the structured summary
        $summary = [];
        foreach ($priorities as $priority) {
            $summary[$priority] = [];
            foreach ($statuses as $status) {
                $count = 0;
                if ($results->has($priority)) {
                    $row = $results[$priority]->firstWhere('status', $status);
                    $count = $row ? (int) $row->count : 0;
                }
                $summary[$priority][$status] = $count;
            }
        }

        return response()->json([
            'date'    => $date,
            'summary' => $summary,
        ], 200);
    }
}
