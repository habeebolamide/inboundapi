<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class GroupController extends Controller
{
    public function create(Request $request)
    {
        // Validate initial request
        $validator = Validator::make($request->all(), [
            "name" => "required|string|max:255",
            "file" => "required|file|mimes:csv,txt|max:10240",
        ]);

        if ($validator->fails()) {
            return sendError('Validation Error.', $validator->errors(), 400);
        }
        $auth_user = Auth::user();

        // DB::beginTransaction();
        try {
            $path = $request->file('file')->getRealPath();
            $data = array_map('str_getcsv', file($path));
            $header = array_map('strtolower', $data[0]);
            unset($data[0]); // Remove header row

            $group = Group::create([
                "name" => $request->name,
                "organization_id" => $auth_user->organization_id,
                "total_members" => 0,
            ]);
            $memberCount = 0;
            foreach ($data as $row) {
                $rowData = array_combine($header, $row);
                // return $rowData;
                Log::info('Processing row:', $rowData);
                $rowValidator = Validator::make($rowData, [
                    'name' => 'required|string|max:255',
                    'email' => 'required|email|unique:users,email',
                    'user_id' => 'required|string|unique:users,user_id'
                ]);

                if ($rowValidator->fails()) {
                    Log::warning('Skipped row due to validation:', $rowValidator->errors()->toArray());
                    continue;
                }

                // ✅ Create user
                $user = User::create([
                    'name' => $rowData['name'],
                    'email' => $rowData['email'],
                    'organization_id' => $auth_user->organization_id,
                    'user_type_id' => 4,
                    'user_id' => $rowData['user_id'],
                    'password' => Hash::make('12345678'),
                ]);

                $group->users()->attach($user->id);
                $memberCount++;
            }

            $group->update(['total_members' => $memberCount]);
            // DB::commit();
            return sendResponse('Users imported and group created successfully.', [
                'imported_users' => $memberCount,
            ], 201);
        } catch (\Throwable $e) {
            // DB::rollBack();
            return sendError('Error occurred while creating group.', ['error' => $e->getMessage()], 500);
        }
    }


    public function getAll()
    {
        // Logic to get all groups

        $group = Group::with('users')->get();
        return sendResponse('Groups retrieved successfully.', $group, 200);
    }

    public function getById($id)
    {
        // Logic to get a group by ID
    }

    public function getOrgGroups(Request $request)
    {
        $authUser = $request->user();
        $groups = Group::where('organization_id', $authUser->organization_id)->get();

        if ($groups->isEmpty()) {
            return sendError('No groups found for this organization.', [], 404);
        }

        return sendResponse('Groups retrieved successfully.', $groups, 200);
    }
}
