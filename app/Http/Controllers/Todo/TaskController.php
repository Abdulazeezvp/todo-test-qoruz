<?php

namespace App\Http\Controllers\Todo;

use App\Http\Controllers\Controller;
use App\Http\Repositories\Todo\TaskRepo;
use App\Models\Task;
use Illuminate\Http\Request;
use Throwable;

class TaskController extends Controller
{
    protected $taskRepo;

    public function __construct(TaskRepo $taskRepo)
    {
        $this->taskRepo = $taskRepo;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        return response([
            'status' => true,
            'data' => $this->taskRepo->getAllTasks($request->status ?? 'all', $request->due ?? '')->toJson()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return response([
            'status' => true,
            'data' => ['parent_tasks' => $this->taskRepo->getParentTasks()->toJson()]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'due_on' => 'required|date|date_format:Y-m-d',
        ]);
        try {
            $this->taskRepo->store($request->all());
        } catch (Throwable $th) {
            return response(
                [
                    'status' => false,
                    'error' => $th->getMessage(),
                ],
                500
            );
        }
        return response(
            [
                'status' => true,
                'messge' => 'created todo task',
            ],
            200
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $task = Task::findOrFail($id);
        return response([
            'status' => true,
            'data' => [
                'parent_tasks' => $this->taskRepo->getParentTasks()->toJson(),
                'task' => $task->toJson()
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);
        $request->validate([
            'title' => 'required|string',
            'due_on' => 'required|date|date_format:Y-m-d',
        ]);
        try {
            $this->taskRepo->update($request->all(), $task);
        } catch (Throwable $th) {
            return response(
                [
                    'status' => false,
                    'error' => $th->getMessage(),
                ]
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        try {
            $this->taskRepo->delete($task);
        } catch (Throwable $th) {
            return response(
                [
                    'status' => false,
                    'error' => $th->getMessage(),
                ],
                500
            );
        }
        return response(
            [
                'status' => true,
                'messge' => 'deleted',
            ],
            200
        );
    }

    public function completed($id)
    {
        $task = Task::findOrFail($id);
        try {
            $this->taskRepo->markAsCompleted($task);
        } catch (Throwable $th) {
            return response(
                [
                    'status' => false,
                    'error' => $th->getMessage(),
                ],
                500
            );
        }
        return response(
            [
                'status' => true,
                'messge' => 'marked as completed',
            ],
            200
        );
    }

    public function titleSearch($s)
    {

        return response(
            [
                'status' => true,
                'data' => [
                    'result' => $this->taskRepo->searchFor($s)->toJson(),
                ],
            ],
            200
        );
    }
}
