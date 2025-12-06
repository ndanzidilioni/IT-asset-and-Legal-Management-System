<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function index(){
        $user = Auth::user();
        if($user->role == 'admin'){
            return Task::all();
        } elseif($user->role == 'developer'){
            return Task::where('developer_id',$user->id)->get();
        } else {
            return Task::where('client_id',$user->id)->get();
        }
    }

    public function store(Request $request){
        $user = Auth::user();
        if($user->role != 'admin') return response()->json(['message'=>'Unauthorized'],403);

        $data = $request->validate([
            'title'=>'required|string',
            'description'=>'required|string',
            'client_id'=>'required|exists:users,id',
            'developer_id'=>'nullable|exists:users,id'
        ]);

        $data['admin_id'] = $user->id;
        $task = Task::create($data);

        return response()->json($task);
    }

    // allow clients to create a task request (no admin required)
    public function clientStore(Request $request){
        $user = Auth::user();
        if($user->role != 'client') return response()->json(['message'=>'Unauthorized'],403);

        $data = $request->validate([
            'title'=>'required|string',
            'description'=>'required|string'
        ]);

        $data['client_id'] = $user->id;
        // admin_id left null until an admin assigns
        $task = Task::create($data);
        return response()->json($task);
    }

    public function update(Request $request, $id){
        $user = Auth::user();
        $task = Task::findOrFail($id);

        if($user->role != 'admin') return response()->json(['message'=>'Unauthorized'],403);

        $data = $request->validate([
            'developer_id'=>'nullable|exists:users,id',
            'status'=>'nullable|in:pending,assigned,in_progress,completed,rejected'
        ]);

        $task->update($data);
        return response()->json($task);
    }

    public function reject($id){
        $user = Auth::user();
        if($user->role != 'developer') return response()->json(['message'=>'Unauthorized'],403);

        $task = Task::findOrFail($id);
        if($task->developer_id != $user->id) return response()->json(['message'=>'Not your task'],403);

        $task->status = 'rejected';
        $task->save();

        return response()->json($task);
    }

    public function destroy($id){
        $user = Auth::user();
        if($user->role != 'admin') return response()->json(['message'=>'Unauthorized'],403);

        $task = Task::findOrFail($id);
        $task->delete();

        return response()->json(['message'=>'Task deleted']);
    }
}
