<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Actions\ThrottleAction;
use App\Http\Requests\UpdatePasswordRequest;
use Symfony\Component\HttpFoundation\Response as Status;

class UpdatePasswordController extends Controller
{
    public function updatePassword(UpdatePasswordRequest $request, ThrottleAction $throttle): JsonResponse
    {
        $user = Auth::user();
        $key = "password-update:" . $user->id;

        $throttle->handle($key, "15min", "passwords.throttled");

        $newPassword = $request->getNewPassword();

        if (Hash::check($newPassword, $user->password)) {
            return response()->json([
                "message" => __("passwords.same_as_current"),
            ], Status::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user->password = Hash::make($newPassword);
        $user->save();

        activity()
            ->performedOn($user)
            ->log("Changed password via profile");

        return response()->json([
            "message" => __("passwords.updated_successfully"),
        ], Status::HTTP_OK);
    }
}
