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
    public function index(): JsonResponse
    {
        return response()->json(User::latest()->paginate(50));
    }

    public function show(User $user): JsonResponse
    {
        return response()->json(new UserResource($user));
    }

    public function update(User $user, UpdateUserRequest $request, UpdateUserAction $updateUserAction): JsonResponse
    {
        return response()->json(['message' => 'Użytkownik zaktualizowany pomyślnie',
            'user' => $updateUserAction($user,$request->validated())], 201);
    }

    public function destroy(User $user): JsonResponse
    {
        return response()->json(['message' => 'Użytkownik został usunięty.',$user->delete()],204);
    }
}
