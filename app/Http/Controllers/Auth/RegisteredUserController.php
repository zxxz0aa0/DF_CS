<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\TaiwanIdNumber;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            // 基本資訊
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:'.User::class,
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],

            // 身分資訊
            'id_number' => ['required', 'string', new TaiwanIdNumber, 'unique:'.User::class],
            'birth_date' => 'required|date|before:today',
            'gender' => 'required|in:male,female,other',

            // 聯絡資訊
            'mobile_phone' => 'required|string|regex:/^09[0-9]{8}$/',
            'home_phone' => 'nullable|string|regex:/^0[0-9]{1,2}-?[0-9]{7,8}$/',
            'address' => 'nullable|string|max:500',

            // 工作資訊
            'department' => 'nullable|string|max:100',
            'position' => 'nullable|string|max:100',

            // 緊急聯絡人
            'emergency_contact' => 'nullable|string|max:100',
            'emergency_phone' => 'nullable|string|regex:/^09[0-9]{8}$/',
        ]);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'id_number' => $request->id_number,
            'birth_date' => $request->birth_date,
            'gender' => $request->gender,
            'mobile_phone' => $request->mobile_phone,
            'home_phone' => $request->home_phone,
            'address' => $request->address,
            'department' => $request->department,
            'position' => $request->position,
            'emergency_contact' => $request->emergency_contact,
            'emergency_phone' => $request->emergency_phone,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
