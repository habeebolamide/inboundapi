<?php

namespace App\Http\Controllers;

use App\Models\CheckIn;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CheckInController extends Controller
{
    public function create(Request $request)
    {
        $authUser = $request->user();
        $validator = Validator::make($request->all(), [
            // 'organization_id' => 'required|exists:organizations,id',
            'group_id' => 'required|exists:groups,id',
            'supervisor_id' => 'required|exists:users,id',
            'title' => 'nullable|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'nullable|integer|min:10|max:500',
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $group = Group::where('id', $request->group_id)
            ->where('organization_id', $authUser->organization_id)
            ->first();

        if (!$group) {
            return response()->json(['message' => 'Invalid group for your organization.'], 403);
        }

        $supervisor = User::where('id', $request->supervisor_id)
            ->where('organization_id', $authUser->organization_id)
            ->first();

        if (!$supervisor) {
            return response()->json(['message' => 'Invalid supervisor for your organization.'], 403);
        }
        $session = CheckIn::create(
            [
                'group_id' => $request->group_id,
                'start_time' => $request->start_time,
                'supervisor_id' => $request->supervisor_id,
                'organization_id' => $authUser->organization_id,
                'title' => $request->title,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'radius' => $request->radius ?? 50,
                'end_time' => $request->end_time,
                'status' => 'scheduled',
            ]
        );

        return response()->json([
            'message' => 'Session created successfully.',
            'data' => $session
        ], 201);
    }

    public function getALl() {
        $authUser = Auth::user();

    // Optionally filter sessions by organization
    $sessions = CheckIn::with(['group', 'supervisor'])
        ->where('organization_id', $authUser->organization_id)
        ->latest('start_time')
        ->get();

    return response()->json([
        'message' => 'Sessions fetched successfully.',
        'data' => $sessions
    ]);
    }
    public function deleteSession($id)
    {
        CheckIn::where('id', $id)->delete();

        return  response()->json([
            'message' => 'Session deleted successfully.',
        ], 201);
    }

    public function update(Request $request)
    {

        $authUser = $request->user();
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:check_ins,id',
            'title' => 'nullable|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'nullable|integer|min:10|max:500',
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $checkIn = CheckIn::where('id', $request->id)
            ->where('organization_id', $authUser->organization_id)
            ->first();

        if (!$checkIn) {
            return response()->json(['message' => 'Invalid check-in for your organization.'], 403);
        }

        $checkIn->update($request->only(['title', 'latitude', 'longitude', 'radius', 'start_time', 'end_time']));

        return response()->json([
            'message' => 'Check-in updated successfully.',
            'data' => $checkIn
        ], 200);
    }

    public function delete($id)
    {
        $authUser = Auth::user();
        $checkIn = CheckIn::where('id', $id)
            ->where('organization_id', $authUser->organization_id)
            ->first();

        if (!$checkIn) {
            return response()->json(['message' => 'Invalid Session for your organization.'], 403);
        }

        $checkIn->delete();

        return sendResponse('Session deleted successfully.', [], 200);
    }
}
