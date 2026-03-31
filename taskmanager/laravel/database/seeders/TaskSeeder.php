<?php

namespace Database\Seeders;

use App\Models\Task;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $today = Carbon::today();

        $tasks = [
            [
                'title'    => 'Review Q1 financial report',
                'due_date' => $today->copy()->addDays(1)->format('Y-m-d'),
                'priority' => 'high',
                'status'   => 'in_progress',
            ],
            [
                'title'    => 'Deploy production hotfix',
                'due_date' => $today->copy()->format('Y-m-d'),
                'priority' => 'high',
                'status'   => 'pending',
            ],
            [
                'title'    => 'Update API documentation',
                'due_date' => $today->copy()->addDays(3)->format('Y-m-d'),
                'priority' => 'medium',
                'status'   => 'pending',
            ],
            [
                'title'    => 'Write unit tests for auth module',
                'due_date' => $today->copy()->addDays(5)->format('Y-m-d'),
                'priority' => 'medium',
                'status'   => 'in_progress',
            ],
            [
                'title'    => 'Clean up dev environment',
                'due_date' => $today->copy()->addDays(7)->format('Y-m-d'),
                'priority' => 'low',
                'status'   => 'done',
            ],
            [
                'title'    => 'Schedule team sync meeting',
                'due_date' => $today->copy()->addDays(2)->format('Y-m-d'),
                'priority' => 'low',
                'status'   => 'pending',
            ],
            [
                'title'    => 'Fix critical login bug',
                'due_date' => $today->copy()->format('Y-m-d'),
                'priority' => 'high',
                'status'   => 'done',
            ],
        ];

        foreach ($tasks as $task) {
            Task::create($task);
        }

        $this->command->info('Seeded ' . count($tasks) . ' tasks successfully.');
    }
}
