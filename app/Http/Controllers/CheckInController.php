<?php

namespace App\Http\Controllers;

use App\Models\AttendanceSession;
use App\Models\CheckIn;
use App\Models\Group;
use App\Models\User;
use Carbon\Carbon;
use Galahad\TimezoneMapper\TimezoneMapper;
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
        $session = AttendanceSession::create(
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
                'building_name' => $request->building_name,
                'status' => 'scheduled',
            ]
        );

        return response()->json([
            'message' => 'Session created successfully.',
            'data' => $session
        ], 201);
    }

    public function Supervisorcreate(Request $request)
    {
        $authUser = $request->user();
        $validator = Validator::make($request->all(), [
            // 'organization_id' => 'required|exists:organizations,id',
            'group_id' => 'required|exists:groups,id',
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

        $session = AttendanceSession::create(
            [
                'group_id' => $request->group_id,
                'start_time' => $request->start_time,
                'supervisor_id' => Auth::id(),
                'organization_id' => $authUser->organization_id,
                'title' => $request->title,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'radius' => $request->radius ?? 50,
                'end_time' => $request->end_time,
                'building_name' => $request->building_name,
                'status' => 'scheduled',
            ]
        );

        return response()->json([
            'message' => 'Session created successfully.',
            'data' => $session
        ], 201);
    }

    public function getAll()
    {
        $authUser = User::where('id', Auth::id())
            ->first();
        $groupIds = $authUser->groups()->pluck('groups.id');

        $sessions = AttendanceSession::with(['group', 'supervisor'])
            ->where('organization_id', $authUser->organization_id)
            ->latest('start_time');

        if ($authUser->user_type_id = 3) {
            $sessions->where('supervisor_id', $authUser->id);
        }else{
            $sessions->whereIn('group_id', $groupIds);
        }
        return response()->json([
            'message' => 'Sessions fetched successfully.',
            'data' => $sessions->get()
        ]);
    }

    public function getAllSessioForSupervisor()
    {
        $authUser = User::where('id', Auth::id())
            ->first();

        $sessions = AttendanceSession::with(['group', 'supervisor'])
            ->where('organization_id', $authUser->organization_id)
            ->where('supervisor_id', $authUser->id)
            ->latest('start_time')
            ->get();
        return response()->json([
            'message' => 'Sessions fetched successfully.',
            'data' => $sessions
        ]);
    }

    public function deleteSession($id)
    {
        AttendanceSession::where('id', $id)->delete();

        return  response()->json([
            'message' => 'Session deleted successfully.',
        ], 201);
    }

    public function update(Request $request)
    {

        $authUser = $request->user();
        $validator = Validator::make($request->all(), [
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

        $checkIn = AttendanceSession::where('id', $request->id)
            ->where('organization_id', $authUser->organization_id)
            ->first();

        if (!$checkIn) {
            return response()->json(['message' => 'Invalid check-in for your organization.'], 403);
        }

        $checkIn->update($request->only(['title', 'latitude', 'longitude', 'radius', 'start_time', 'end_time', 'building_name']));

        return response()->json([
            'message' => 'Check-in updated successfully.',
            'data' => $checkIn
        ], 200);
    }

    public function delete($id)
    {
        $authUser = Auth::user();
        $checkIn = AttendanceSession::where('id', $id)
            ->where('organization_id', $authUser->organization_id)
            ->first();

        if (!$checkIn) {
            return response()->json(['message' => 'Invalid Session for your organization.'], 403);
        }

        $checkIn->delete();

        return sendResponse('Session deleted successfully.', [], 200);
    }

    public function getTodaySessions(Request $request)
    {
        $authUser = $request->user();
        $today = now()->startOfDay();

        $sessions = AttendanceSession::with(['group', 'supervisor'])
            ->where('organization_id', $authUser->organization_id)
            ->whereDate('start_time', $today)
            ->latest('start_time')
            ->orderBy('start_time', 'asc')
            ->get();
        $sessions->map(function ($session) use ($authUser) {
            // Check if the user has checked in for this session
            $checkin = Checkin::where(['user_id' => $authUser->id, "attendance_session_id" => $session->id])
                ->whereDate('created_at', now()->toDateString()) // Ensure it's today's check-in
                ->first();

            // Inject the check-in data (yes if checked in, no if not)
            $session->checkin_status = $checkin ? 'yes' : 'no';

            return $session;
        });


        return sendResponse('Today\'s sessions retrieved successfully.', $sessions, 200);
    }

    public function checkIn(Request $request)
    {
        $session = AttendanceSession::where('id', $request->sessionId)->first();
        $user = Auth::user();
        $distance = calculateDistance($request->latitude, $request->longitude, $session->latitude, $session->longitude);

        if ($distance >= $session->radius) {
            return sendError("You are not within range", [], 404);
        }
        if ($session->status != "ongoing") {
            return sendError("Not In Session", [], 404);
        }

        $timezoneMapper = new TimezoneMapper();
        $mapped_timezone = $timezoneMapper->mapCoordinates($request->latitude, $request->longitude, 'Europe/London');
        // return $mapped_timezone;
        $currentTime = Carbon::now();

        $timezoneTime = $currentTime->setTimezone($mapped_timezone);
        $formattedTime = $timezoneTime->format('Y-m-d H:i:s');
        // return $formattedTime;
        if ($formattedTime < $session->start_time || $formattedTime > $session->end_time) {
            return sendError("You are not within the session time", [], 404);
        }

        $checkin = Checkin::updateOrCreate(
            ['user_id' => $user->id],
            [
                "attendance_session_id" => $request->sessionId,
                "checked_in_at" => $formattedTime
            ]
        );

        return sendResponse("You've checkedin successfully", $checkin, 200);
    }

    public function endSession(Request $request)
    {
        $session = AttendanceSession::where('id', $request->sessionId)->first();
        if (!$session) {
            return sendError("Session not found", [], 404);
        }

        if ($session->status != "ongoing") {
            return sendError("Session is not ongoing", [], 404);
        }

        $session->status = 'ended';
        $session->save();

        return sendResponse("Session ended successfully", $session, 200);
    }

    public function startSession(Request $request)
    {
        $session = AttendanceSession::where('id', $request->sessionId)->first();

        if (!$session) {
            return sendError("Session not found", [], 404);
        }

        if ($session->status != "scheduled") {
            return sendError("Session is not scheduled", [], 404);
        }

        $currentTime = now();

        if ($currentTime < $session->start_time || $currentTime > $session->end_time) {
            return sendError("Session can only be started within the scheduled time range", [], 400);
        }

        $session->status = 'ongoing';
        $session->save();

        return sendResponse("Session started successfully", $session, 200);
    }

}
