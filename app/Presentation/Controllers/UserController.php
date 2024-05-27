<?php
namespace App\Presentation\Controllers;

use Illuminate\Http\Request;
use App\Presentation\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/user",
     *     tags={"auth"},
     *     summary="Get authenticated user",
     *     description="Returns the currently authenticated user",
     *     @OA\Response(
     *         response=200,
     *         description="Authenticated user",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", example="johndoe@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function user(Request $request)
    {
        return response()->json(Auth::user());
    }
}
