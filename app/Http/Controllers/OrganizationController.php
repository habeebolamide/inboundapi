<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class OrganizationController extends Controller
{
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
            'organization_id' => 'required|exists:organizations,id',
            "supervisor_csv" => "required|file|mimes:csv|max:10240",
        ]);

        if ($validator->fails()) {
            return sendError('Validation Error.', $validator->errors(), 400);
        }

        $path = $request->file('supervisor_csv')->getRealPath();
        $data = array_map('str_getcsv', file($path));

        $header = array_map('strtolower', $data[0]);
        unset($data[0]); // Remove header

        foreach ($data as $row) {
            $rowData = array_combine($header, $row);
            // return $rowData; // Debugging line, remove in production
            $validator = Validator::make($rowData, [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
            ]);

            if ($validator->fails()) {
                continue; // skip invalid rows or handle error
            }

            User::create([
                'name' => $rowData['name'],
                'email' => $rowData['email'],
                'organization_id' => $request->organization_id,
                'user_type_id' => 3, 
                'user_id' => $rowData['user_id'],
                'password' => bcrypt('12345678'), 
            ]);
        }

        return sendResponse('Operation successfully.', [], 201);
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
}
