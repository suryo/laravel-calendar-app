<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'task' => 'required|string',
            'level' => 'required|string',
            'priority' => 'required|string',
            'start_date' => 'required|date',
            'dateline' => 'required|date',
            'status' => 'required|string',
        ]);

        Task::create($request->all());

        return redirect()->back()->with('success', 'Task created successfully.');
    }
}
