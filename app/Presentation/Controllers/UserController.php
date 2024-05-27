<?php
namespace App\Presentation\Controllers;

use Illuminate\Http\Request;
use App\Presentation\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function user(Request $request)
    {
        return response()->json(Auth::user());
    }
}
