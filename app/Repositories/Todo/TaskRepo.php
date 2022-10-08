<?php

namespace App\Http\Repositories\Todo;

use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

class TaskRepo
{
    public function store(array $data)
    {
        DB::beginTransaction();
        try {
            $task = Task::create([
                'title' => $data['title'],
                'description' => $data['description'],
                'parent_id' => $data['parent_id'] ?? 0,
                'status' => $data['is_completed'] ?? false,
                'due_on' => $data['due_on']
            ]);
        } catch (Throwable $th) {
            DB::rollBack();
            throw $th;
        }
        DB::commit();
        return true;
    }

    public function update(array $data, Task $task)
    {
        DB::beginTransaction();
        try {
            $task->title = $data['title'];
            $task->description = $data['description'];
            $task->parent_id = $data['parent_id'];
            $task->status = $data['is_completed'] == 1 || $data['is_completed'] == true ? true : false;
            $task->due_on = $data['due_on'];
            $task->save();
        } catch (Throwable $th) {
            DB::rollBack();
            throw $th;
        }
        DB::commit();
        return $task;
    }

    public function delete(Task $task)
    {
        DB::beginTransaction();
        try {
            $task->delete();
        } catch (Throwable $th) {
            DB::rollBack();
            throw $th;
        }
        DB::commit();
        return true;
    }

    public function forceDelete(Task $task)
    {
        DB::beginTransaction();
        try {
            $task->forceDelete();
        } catch (Throwable $th) {
            DB::rollBack();
            throw $th;
        }
        DB::commit();
        return true;
    }

    public function deleteAllOldCompleted()
    {
        $oneMonthBefore = Carbon::now()->subMonths(1);
        $allDeleted = Task::whereDate('updated_at', '<=', $oneMonthBefore->toDateString())->where('status', true);
        try {
            foreach ($allDeleted as $task) {
                $this->forceDelete($task);
            }
        } catch (Throwable $th) {
        }
    }

    public function getAllTasks($status = 'all', $due = 'all')
    {
        $task = Task::select('*');
        if ($status != 'all') {
            $task->where('status', $status == 0 || $status == false ? false : true);
        }
        if ($due != 'all') {
            if ($due == 'overdue') {
                $task->whereDate('due_on', '<', now()->toDateString())->$task->where('status', false);
            } else if ($due == 'today') {
                $task->whereDate('due_on', '=', now()->toDateString())->$task->where('status', false);
            }
        }
        return $task->orderBy('due_on', 'DESC')->groupBy('parent_id')->get();
    }

    public function getParentTasks($is_pending = true)
    {
        $task = Task::select('*')->where('parent_id', 0);
        if ($is_pending) {
            $task->where('status', false);
        }
        return $task->get();
    }

    public function markAsCompleted(Task $task)
    {
        DB::beginTransaction();
        try {
            $task->status = true;
            $task->save();
            $allTasks = Task::where('parent_id', $task->id)->where('status', false)->get();
            foreach ($allTasks as $subTask) {
                $subTask->status = true;
                $subTask->save();
            }
        } catch (Throwable $th) {
            DB::rollBack();
            throw $th;
        }
        DB::commit();
        return true;
    }
}
