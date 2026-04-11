<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Mail\MembreJoined;
use Illuminate\Http\Request;
use App\Models\Activity;
use App\Models\Comment;
use Illuminate\Support\Facades\Mail;
use App\Models\User;

class ActivityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $userId = auth()->user()->id;
        $validated = $request->validate([
            "search" => "nullable|string",
            "category" => "nullable|string",
        ]);

        $activitiesQuery = Activity::with('participants','comments.user:id,name,avatar,email')->where('status','!=','cancelled')->where('host_id', '!=', $userId);

        if ($validated["search"] !== null){
            $searchValue = $validated['search'];
            $activitiesQuery = $activitiesQuery->where('title','LIKE',"%$searchValue%")->
            orWhere('city','LIKE',"%$searchValue%")->
            orWhere('category','LIKE',"%$searchValue%");
        }
        if ($validated["category"] !== null){
            $activitiesQuery->where('category','=',$validated['category']);
        }
        $activities = $activitiesQuery->get()->map(function($activity) use ($userId){
            $activity->joined = $activity->participants->contains('id',$userId);
            return $activity;
        });;

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
            $activitiesQuery = Activity::where('host_id', $userId);
        }else if ($filter == "membre"){
            $activitiesQuery = Activity::whereHas('participants', function($query) use ($userId){
                $query->where('user_id',$userId);
            });
        }else if ($filter == "both"){
            $activitiesQuery = Activity::where('host_id', $userId)->orWhereHas('participants', function($query) use ($userId){
                $query->where('user_id',$userId);
                });
        }
        $activities = $activitiesQuery->with("participants","comments.user:id,name,email,avatar")->get();
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
            'city' => "required|string|max:255",
            'latitude' => "required|numeric|max:255",
            'longitude' => "required|numeric|max:255",
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
        $activity = Activity::findOrFail($id);
        $activity->update($request['activity']);
        return response()->json([
            "status" => "success",
            "activity" => $activity
        ]);
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
        $activity = Activity::findOrFail($id);
        $host = $activity->host;

        Mail::to($host)->send(new MembreJoined($activity,$user));

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

    public function removeMembre(Request $request, string $id)
    {
        $user = User::findOrFail($id);
        $activityId = $request["activityId"];
        if ($user->joinedActivities()->where('activities.id',$activityId)->exists()){
            $user->joinedActivities()->detach($activityId);
            return response()->json([
                "status" => "success",
                "message" => "user removed successfully !"
            ],200);
        }

        return response()->json([
            "status" => "error",
            "message" => "uses didn't joined this activity !"
        ],401);
    }

    public function comment(Request $request, string $id)
    {
        $userId = request()->user()->id;
        $validated = $request->validate([
            "content" => "required|string"
        ]);
        $validated["user_id"] = $userId;
        $validated["activity_id"] = $id;
        $comment = Comment::create($validated);

        return response()->json([
            "status" => "success",
            "message" => "comment created !"
        ],201);
    }

    public function deleteComment(string $id)
    {
        $comment = Comment::findOrFail($id)->delete();
        return response()->json([
            "status" => "success",
            "message" => "comment deleted !"
        ],204);
    }
}
