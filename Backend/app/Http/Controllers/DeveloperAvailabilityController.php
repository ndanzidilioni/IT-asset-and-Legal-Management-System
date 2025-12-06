<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DeveloperAvailability;
use Illuminate\Support\Facades\Auth;

class DeveloperAvailabilityController extends Controller
{
    public function store(Request $request){
        $user = Auth::user();
        if($user->role != 'developer') return response()->json(['message'=>'Unauthorized'],403);

        $data = $request->validate([
            'availability'=>'required|json'
        ]);

        $availability = DeveloperAvailability::updateOrCreate(
            ['developer_id'=>$user->id],
            ['availability'=>$data['availability']]
        );

        return response()->json($availability);
    }

    public function show(){
        $user = Auth::user();
        if($user->role != 'developer') return response()->json(['message'=>'Unauthorized'],403);

        $availability = DeveloperAvailability::where('developer_id',$user->id)->first();
        return response()->json($availability);
    }
}
