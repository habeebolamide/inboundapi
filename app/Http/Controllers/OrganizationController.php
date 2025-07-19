<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
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
}
