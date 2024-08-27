<?php

namespace App\Http\Controllers;

use DataTables;
use App\Models\Todolist;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TodolistController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            $data = Todolist::select('id', 'task', 'status')->get();
            return Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('action', function($row){
                        $editBtn = $row->status === 'done' ? '' : '<a href="javascript:void(0)" data-id="'.$row->id.'" class="edit btn btn-primary btn-sm"><i class="fa fa-edit"></i></a>';
                        $deleteBtn = '<a href="javascript:void(0)" data-id="'.$row->id.'" class="delete btn btn-danger btn-sm"><i class="fa fa-trash"></i></a>';
                        return $editBtn . ' ' . $deleteBtn;
                    })
                    ->rawColumns(['action'])
                    ->make(true);
        }
        return view('to-do-list');
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'task' => 'required|string|unique:todolists,task'
        ]);

        $todList = new Todolist();
        $todList->task = $request->task;
        $todList->save();        

        return response()->json(['message' => 'Task added successfully.']);
    }

    public function updateStatus($id): JsonResponse
    {
        $task = Todolist::find($id);
        if ($task) {
            $task->status = 'done';
            $task->save();
            return response()->json(['success' => true, 'message' => 'Task marked as done.']);
        } else {
            return response()->json(['success' => false, 'message' => 'Task not found.']);
        }
    }

    public function markAll(Request $request): JsonResponse
    {
        if ($request->ajax()) {
            $currentStatus = Todolist::whereNotNull('status')->first()->status ?? null;
            $newStatus = $currentStatus === 'done' ? null : 'done';
            Todolist::query()->update(['status' => $newStatus]);
            return response()->json(['success' => true, 'message' => 'All tasks status have been updated']);
        }
    }
    
    public function destroy($id): JsonResponse
    {
        $task = Todolist::find($id);
        if ($task) {
            $task->delete();
            return response()->json(['success' => true, 'message' => 'Task deleted successfully.']);
        } else {
            return response()->json(['success' => false, 'message' => 'Task not found.']);
        }
    }
}
