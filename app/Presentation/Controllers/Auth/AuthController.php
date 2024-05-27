<?php
namespace App\Presentation\Controllers\Auth;

use Illuminate\Http\Request;
use App\Presentation\Controllers\Controller;
use Illuminate\Support\Facades\Auth as BaseAuth;

class AuthController extends Controller
{
    public function user(Request $request)
    {
        return response()->json(BaseAuth::user());
    }
}
