<?php

namespace App\Http\Controllers;

use App\Models\UserModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index() {
        
        $jumlahPengguna = UserModel::count();

        return view('user', ['jumlahPengguna' => $jumlahPengguna]);
    }
}
