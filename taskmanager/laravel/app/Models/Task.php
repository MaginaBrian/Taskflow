<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'due_date',
        'priority',
        'status',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    /**
     * Priority sort order map (high=1, medium=2, low=3 for ascending sort by priority weight desc)
     */
    public static array $priorityOrder = [
        'high'   => 1,
        'medium' => 2,
        'low'    => 3,
    ];

    /**
     * Valid status transitions: pending -> in_progress -> done
     */
    public static array $statusTransitions = [
        'pending'     => 'in_progress',
        'in_progress' => 'done',
        'done'        => null,
    ];

    /**
     * Get the next valid status for this task.
     */
    public function nextStatus(): ?string
    {
        return self::$statusTransitions[$this->status] ?? null;
    }

    /**
     * Check if a given status is a valid next step from current status.
     */
    public function canTransitionTo(string $newStatus): bool
    {
        return self::$statusTransitions[$this->status] === $newStatus;
    }

    /**
     * Scope to sort tasks by priority (high first) then due_date ascending.
     */
    public function scopeSortedByPriority($query)
    {
        return $query->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 WHEN 'low' THEN 3 END")
            ->orderBy('due_date', 'asc');
    }

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus($query, ?string $status)
    {
        if ($status) {
            $query->where('status', $status);
        }
        return $query;
    }
}
