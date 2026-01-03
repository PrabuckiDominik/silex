<?php

namespace App\Http\Controllers;

use App\Http\Actions\User\CreateUserAction;
use App\Http\Actions\User\UpdateUserAction;
use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class UserController extends Controller
{
    use AuthorizesRequests;
    public function __construct()
    {
        $this->authorizeResource(User::class, 'user');
    }
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        return response()->json(User::latest()->paginate(50));
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): JsonResponse
    {
        return response()->json(new UserResource($user));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(User $user, UpdateUserRequest $request, UpdateUserAction $updateUserAction): JsonResponse
    {
        return response()->json(['message' => 'Użytkownik zaktualizowany pomyślnie',
            'user' => $updateUserAction($user,$request->validated())], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): JsonResponse
    {
        return response()->json(['message' => 'Użytkownik został usunięty.',$user->delete()],204);
    }
}
