<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskStoreUpdateValidation;
use App\Services\TaskService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class TaskController extends Controller
{

    protected $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    public function index()
    {
        $user = auth()->user();

        $tasks = $this->taskService->getAll($user);

        return response()->json($tasks);
    }

    public function store(TaskStoreUpdateValidation $request)
    {
        $data = $request->validated();

        try {
            $task = $this->taskService->createNewTask($data);

            return response()->json($task, Response::HTTP_CREATED);
        } catch (ValidationException $ex) {
            return response()->json(['message' => $ex->response], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function show(int $id)
    {
        try {
            $task = $this->taskService->getTask($id);

            return response()->json($task);
        } catch (ModelNotFoundException $ex) {
            return response()->json(['message' => $ex->getMessage()], Response::HTTP_NOT_FOUND);
        }
    }

    public function update(TaskStoreUpdateValidation $request, int $id)
    {
        $data = $request->validated();

        try {
            $task = $this->taskService->updateTask($data, $id);

            return response()->json($task);
        } catch (ModelNotFoundException $ex) {
            return response()->json(['message' => $ex->getMessage()], Response::HTTP_NOT_FOUND);
        }
    }

    public function destroy(int $id)
    {
        try {
            $result = $this->taskService->deleteTask($id);

            return response()->json(['success' => $result], Response::HTTP_NO_CONTENT);
        } catch (ModelNotFoundException $ex) {
            return response()->json(['message' => $ex->getMessage()], Response::HTTP_NOT_FOUND);
        }
    }
}
