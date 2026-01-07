<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Actions\User\CreateUserAction;
use Illuminate\Http\JsonResponse;
use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response as Status;

class UserManagementController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize("viewAny", User::class);

        $users = User::query()
            ->role(Role::User->value)
            ->orderBy("id")
            ->get();

        return response()->json(UserResource::collection($users), Status::HTTP_OK);
    }

    public function show(User $user): JsonResponse
    {
        $this->authorize("view", $user);

        return response()->json(new UserResource($user), Status::HTTP_OK);
    }

    public function store(StoreUserRequest $request, CreateUserAction $registerUser): JsonResponse
    {
        $this->authorize("create", User::class);

        $user = $registerUser($request->validated());


        if (!$user) {
            return response()->json([
                "message" => __("users.email_exists"),
            ], Status::HTTP_CONFLICT);
        }

        $user->syncRoles(Role::User->value);
        return response()->json(new UserResource($user), Status::HTTP_CREATED);
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $this->authorize("update", $user);
        $mail = $request->input("email");

        $emailChanged = $mail !== null && $mail !== $user->email;

        $updateData = [
            "name" => $request->name ?? $user->first_name,
            "email" =>$request->email ?? $user->email,
        ];

        $user->update($updateData);

        if ($emailChanged) {
            $user->email_verified_at = null;
            $user->save();
            $user->sendEmailVerificationNotification();
        }

        return response()->json(new UserResource($user), Status::HTTP_OK);
    }

    public function destroy(User $user): JsonResponse
    {
        $this->authorize("delete", $user);

        $user->delete();

        return response()->json(["message" => __("users.deleted_successfully")], Status::HTTP_OK);
    }
}
