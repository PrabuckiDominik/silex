<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response as Status;

class UserProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            "message" => __("profile.retrieved"),
            "data" => new UserResource($user),
        ])->setStatusCode(Status::HTTP_OK);
    }

    public function update(UpdateUserRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->update($request->validated());

        return response()->json([
            "message" => __("profile.updated"),
            "data" => new UserResource($user),
        ])->setStatusCode(Status::HTTP_OK);
    }

}
