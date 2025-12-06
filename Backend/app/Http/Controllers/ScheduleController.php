<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    // GET /api/schedules?developer_id=&from=&to=
    public function index(Request $request){
        $user = Auth::user();
        $q = Schedule::query();

        if($request->has('developer_id')) $q->where('developer_id',$request->developer_id);
        if($request->has('from')) $q->where('end','>=',Carbon::parse($request->from));
        if($request->has('to')) $q->where('start','<=',Carbon::parse($request->to));

        if($user->role === 'admin'){
            return $q->get();
        }

        if($user->role === 'developer'){
            return $q->where('developer_id',$user->id)->get();
        }

        return response()->json(['message'=>'Unauthorized'],403);
    }

    public function show($id){
        $user = Auth::user();
        $s = Schedule::findOrFail($id);
        if($user->role === 'admin' || ($user->role==='developer' && $s->developer_id == $user->id)){
            return $s;
        }
        return response()->json(['message'=>'Unauthorized'],403);
    }

    public function store(Request $request){
        $user = Auth::user();
        // allow admin or developer to create
        if(!in_array($user->role,['admin','developer'])) return response()->json(['message'=>'Unauthorized'],403);

        $data = $request->validate([
            'developer_id'=>'required|exists:users,id',
            'task_id'=>'nullable|exists:tasks,id',
            'start'=>'required|date',
            'end'=>'required|date|after:start',
            'type'=>'nullable|string',
            'status'=>'nullable|string',
            'notes'=>'nullable|string'
        ]);

        // conflict check: no overlapping schedule for this developer
        $conflict = Schedule::where('developer_id',$data['developer_id'])
            ->where(function($q) use ($data){
                $q->where('start','<',$data['end'])->where('end','>',$data['start']);
            })->exists();

        if($conflict) return response()->json(['message'=>'Conflict'],409);

        $s = Schedule::create($data);
        return response()->json($s);
    }

    public function update(Request $request,$id){
        $user = Auth::user();
        $s = Schedule::findOrFail($id);
        if(!($user->role==='admin' || ($user->role==='developer' && $s->developer_id==$user->id))) return response()->json(['message'=>'Unauthorized'],403);

        $data = $request->validate([
            'developer_id'=>'nullable|exists:users,id',
            'task_id'=>'nullable|exists:tasks,id',
            'start'=>'nullable|date',
            'end'=>'nullable|date|after:start',
            'type'=>'nullable|string',
            'status'=>'nullable|string',
            'notes'=>'nullable|string'
        ]);

        // conflict check for updated times (exclude this schedule)
        if(isset($data['start']) || isset($data['end']) || isset($data['developer_id'])){
            $devId = $data['developer_id'] ?? $s->developer_id;
            $start = isset($data['start']) ? Carbon::parse($data['start']) : Carbon::parse($s->start);
            $end = isset($data['end']) ? Carbon::parse($data['end']) : Carbon::parse($s->end);

            $conflict = Schedule::where('developer_id',$devId)
                ->where('id','<>',$s->id)
                ->where(function($q) use ($start,$end){
                    $q->where('start','<',$end->toDateTimeString())->where('end','>',$start->toDateTimeString());
                })->exists();

            if($conflict) return response()->json(['message'=>'Conflict'],409);
        }

        $s->update($data);
        return response()->json($s);
    }

    public function destroy($id){
        $user = Auth::user();
        $s = Schedule::findOrFail($id);
        if(!($user->role==='admin' || ($user->role==='developer' && $s->developer_id==$user->id))) return response()->json(['message'=>'Unauthorized'],403);
        $s->delete();
        return response()->json(['message'=>'deleted']);
    }

    // GET /api/schedules/free-slots?developer_id=&from=&to=&duration=minutes
    public function freeSlots(Request $request){
        $user = Auth::user();
        if($user->role !== 'admin' && $user->role !== 'developer') return response()->json(['message'=>'Unauthorized'],403);

        $devId = $request->developer_id ?? ($user->role==='developer' ? $user->id : null);
        if(!$devId) return response()->json(['message'=>'developer_id required'],400);

        $from = Carbon::parse($request->from ?? now());
        $to = Carbon::parse($request->to ?? $from->copy()->addDays(7));
        $duration = intval($request->duration ?? 60); // minutes

        $schedules = Schedule::where('developer_id',$devId)->where(function($q) use ($from,$to){
            $q->where('start','<=',$to)->where('end','>=',$from);
        })->orderBy('start')->get();

        // naive free slot finder between from..to where schedule entries mark busy times (status != 'free')
        $slots = [];
        $cursor = $from->copy();

        foreach($schedules as $sch){
            $sstart = Carbon::parse($sch->start);
            $send = Carbon::parse($sch->end);
            if($cursor->diffInMinutes($sstart) >= $duration){
                $slots[] = ['start'=>$cursor->toDateTimeString(),'end'=>$sstart->toDateTimeString()];
            }
            if($send->gt($cursor)) $cursor = $send->copy();
            if($cursor->gt($to)) break;
        }

        if($cursor->diffInMinutes($to) >= $duration){
            $slots[] = ['start'=>$cursor->toDateTimeString(),'end'=>$to->toDateTimeString()];
        }

        return response()->json($slots);
    }

    // Book a free slot for a task (convenience endpoint)
    public function book(Request $request){
        $request->validate([
            'task_id'=>'required|exists:tasks,id',
            'developer_id'=>'required|exists:users,id',
            'start'=>'required|date',
            'end'=>'required|date|after:start',
            'type'=>'nullable|string',
            'notes'=>'nullable|string'
        ]);

        $data = $request->only(['task_id','developer_id','start','end','type','notes']);

        // double-check conflict
        $conflict = Schedule::where('developer_id',$data['developer_id'])
            ->where(function($q) use ($data){
                $q->where('start','<',$data['end'])->where('end','>',$data['start']);
            })->exists();

        if($conflict) return response()->json(['message'=>'Conflict'],409);

        $s = Schedule::create([
            'developer_id'=>$data['developer_id'],
            'task_id'=>$data['task_id'],
            'start'=>$data['start'],
            'end'=>$data['end'],
            'type'=>$data['type'] ?? 'block',
            'status'=>'booked',
            'notes'=>$data['notes'] ?? null
        ]);

        return response()->json($s,201);
    }
}
