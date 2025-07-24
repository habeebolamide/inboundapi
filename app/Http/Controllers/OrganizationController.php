<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Organization;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class OrganizationController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
        if ($validator->fails()) {
            return sendError('Validation Error.', $validator->errors(), 400);
        }

        $credentials = request(['email', 'password']);

        if (!Auth::attempt($credentials)) {
            return sendError('Invalid email or password', [], 400);
        }
        if (Auth::user()->user_type_id !== 2) {
            return sendError('Unauthorized. Admins only.', [], 403);
        }
        $success['user'] = User::where('email', $request->email)->first();
        $tokenResult = $success['user']->createToken('API TOKEN');
        $success['token'] = $tokenResult->plainTextToken;

        return sendResponse('Login Successfull', $success, 200);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "name" => "required|string|max:255",
            "slug" => "required|string|max:255|unique:organizations,slug",
            "email" => "required|email|max:255|unique:organizations,email",
            "type" => "required|string|in:school,company,ngo",
            "address" => "nullable|string|max:255",
            "password" => "required|string|min:6|confirmed",
        ]);

        if ($validator->fails()) {
            return sendError('Validation Error.', $validator->errors(), 400);
        }

        $organization = Organization::create([
            "name" => $request->name,
            "slug" => $request->slug,
            "email" => $request->email,
            "type" => $request->type,
            "address" => $request->address,
        ]);

        $adminType = UserType::where('name', 'admin')->first();

        $user = User::create([
            "name" => "Admin of " . $organization->name,
            "email" => $request->email,
            "password" => Hash::make($request->password),
            "user_type_id" => $adminType->id,
            "user_id" => "admin_" . $organization->slug,
            "organization_id" => $organization->id,
        ]);

        $token = $user->createToken("auth_token")->plainTextToken;

        return sendResponse('Organization and admin user created.', [
            'token' => $token,
            'user' => $user,
            'organization' => $organization,
        ], 201);
    }

    public function CreateSupervisors(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // 'organization_id' => 'required|exists:organizations,id',
            "supervisor_csv" => "required|file|mimes:csv|max:10240",
        ]);

        if ($validator->fails()) {
            return sendError('Validation Error.', $validator->errors(), 400);
        }
        $auth_user = Auth::user();
        $path = $request->file('supervisor_csv')->getRealPath();
        $data = array_map('str_getcsv', file($path));

        $header = array_map('strtolower', $data[0]);
        unset($data[0]); 
        $group = Group::create([
                "name" => "Supervisors",
                "organization_id" => $auth_user->organization_id,
                "total_members" => 0,
        ]);
        $memberCount = 0;
        foreach ($data as $row) {
            $rowData = array_combine($header, $row);
            // return $rowData;
            $validator = Validator::make($rowData, [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
            ]);

            if ($validator->fails()) {
                Log::warning('Skipped row due to validation:', $rowData);
                continue; 
            }

            $user = User::create([
                'name' => $rowData['name'],
                'email' => $rowData['email'],
                'organization_id' => $auth_user->organization_id,
                'user_type_id' => 3, 
                'user_id' => $rowData['user_id'],
                'password' => Hash::make('12345678'), 
            ]);
            $group->users()->attach($user->id);
            $memberCount++;
        }
        $group->update(['total_members' => $memberCount]);

        return sendResponse('Operation successfull.', [], 201);
    }

    public function getSupervisors(Request $request)
    {
        $organizationId = Auth::user()->organization_id;

        if (!$organizationId) {
            return sendError('Organization ID is required.', [], 400);
        }

        $supervisors = User::where('organization_id', $organizationId)
            ->where('user_type_id', 3) 
            ->get();

        return sendResponse('Supervisors retrieved successfully.', $supervisors, 200);
    }

    public function getOrganizationSupervisors(Request $request)
    {
        $organizationId = Auth::user()->organization_id;

        if (!$organizationId) {
            return sendError('Organization ID is required.', [], 400);
        }

        $supervisors = User::where('organization_id', $organizationId)
            ->where('user_type_id', 3) 
            ->get();

        return sendResponse('Supervisors retrieved successfully.', $supervisors, 200);
    }
}
