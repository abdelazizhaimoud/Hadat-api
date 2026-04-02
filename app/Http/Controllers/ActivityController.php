<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Activity;
use PDO;

class ActivityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $userId = auth()->user()->id;

        $activities = Activity::with('participants')->where('host_id', '!=', $userId)->get()->map(function($activity) use ($userId){
            $activity->joined = $activity->participants->contains('id',$userId);
            return $activity;
        });
        return response()->json([
            "status" => "success",
            "activities" => $activities
        ],200);
    }

    public function userActivities(Request $request)
    {
        $userId = auth()->user()->id;
        $validated = $request->validate([
            "filter" => "required|string",
        ]);
        $filter = $validated["filter"];
        if ($filter == "hosted"){
            $activities = Activity::where('host_id', $userId)->get();
        }else if ($filter == "membre"){
            $activities = Activity::whereHas('participants', function($query) use ($userId){
                $query->where('user_id',$userId);
            })->get();
        }else if ($filter == "both"){
            $activities = Activity::where('host_id', $userId)->orWhereHas('participants', function($query) use ($userId){
                $query->where('user_id',$userId);
                })->get();
        }
        return response()->json([
            "status" => "success",
            "activities" => $activities
        ],200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $userId = auth()->user()->id;
        $request['host_id'] = $userId;
        $validated = $request->validate([
            'title' => "required|string|min:3|max:255",
            'category' => "required|string|max:255",
            'location' => "required|string|max:255",
            'date_time' => "required|date",
            'max_participants' => "required|integer|max:20",
            'host_id' => "required|integer",
        ]);

        $activity = Activity::create($validated);

        return response()->json([
            "status" => "success",
            "activity" => $activity
        ],201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $activity = Activity::whereId($id)->first();
        if ($activity === null){
            return response()->json([
                "status" => "error",
                "message" => "activity not found !"
            ],404);
        }
        $activity->load('participants','host');
        return response()->json([
            "status" => "success",
            "activity" => $activity
        ],200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function join(string $id)
    {
        $user = auth()->user();

        if ($user->hostedActivities()->where('activities.id',$id)->exists()){
            return response()->json([
                "status" => "error",
                "message" => "user host this activity",
            ],401);
        }

        if ($user->joinedActivities()->where('activities.id',$id)->exists()){
            return response()->json([
                "status" => "error",
                "message" => "user already joined this activity !"
            ],401);
        }
                
        $user->joinedActivities()->attach($id);

        return response()->json([
            "status" => "success",
            "message" => "joined successfully !"
        ],200);
    }


    public function leave(string $id)
    {
        $user = auth()->user();
        if ($user->joinedActivities()->where('activities.id',$id)->exists()){
            $user->joinedActivities()->detach($id);
            return response()->json([
                "status" => "success",
                "message" => "user left successfully !"
            ],200);
        }

        return response()->json([
            "status" => "error",
            "message" => "uses isn't joined to this activity !"
        ],401);
    }
}
