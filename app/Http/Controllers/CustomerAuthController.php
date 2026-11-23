<?php

namespace App\Http\Controllers;

use App\Models\AbandonedCheckout;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerAuthController extends Controller
{
    public static function enabled(): bool
    {
        return (bool) Setting::get('customer_login_enabled', true);
    }

    private function guardEnabled(): void
    {
        abort_unless(static::enabled(), 404);
    }

    public function showLogin()
    {
        $this->guardEnabled();

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $this->guardEnabled();

        $data = $request->validate([
            'phone' => ['required', 'string', 'max:25'],
            'password' => ['required', 'string', 'max:100'],
        ]);

        $phone = AbandonedCheckout::normalizePhone($data['phone']);

        // শুধু কাস্টমার রোল — অ্যাডমিন এই ফর্ম দিয়ে ঢুকতে পারবে না
        if (! $phone || ! Auth::attempt(['phone' => $phone, 'password' => $data['password'], 'role' => 'customer'], $request->boolean('remember'))) {
            return back()->withInput($request->only('phone'))->withErrors(['phone' => 'ফোন নম্বর বা পাসওয়ার্ড ভুল।']);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('account.index'));
    }

    public function showRegister()
    {
        $this->guardEnabled();

        return view('auth.register');
    }

    public function register(Request $request)
    {
        $this->guardEnabled();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:25'],
            'password' => ['required', 'string', 'min:6', 'max:100', 'confirmed'],
        ]);

        $phone = AbandonedCheckout::normalizePhone($data['phone']);

        if (! $phone) {
            return back()->withInput($request->only('name', 'phone'))->withErrors(['phone' => 'সঠিক বাংলাদেশি মোবাইল নম্বর দিন (যেমন 017XXXXXXXX)।']);
        }

        if (User::where('phone', $phone)->exists()) {
            return back()->withInput($request->only('name', 'phone'))->withErrors(['phone' => 'এই ফোন নম্বরে আগেই একাউন্ট আছে। সাইন ইন করুন।']);
        }

        $user = new User(['name' => $data['name'], 'phone' => $phone, 'password' => $data['password']]);
        $user->role = 'customer'; // ইনপুট থেকে কখনো role নেওয়া হয় না
        $user->save();

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('account.index');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    public function account()
    {
        $this->guardEnabled();

        $user = Auth::user();

        if ($user->role !== 'customer') {
            return redirect('/admin');
        }

        $orders = $user->orders()->with('items')->latest()->paginate(10);

        return view('account.index', compact('user', 'orders'));
    }

    public function updateAccount(Request $request)
    {
        $this->guardEnabled();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
            'password' => ['nullable', 'string', 'min:6', 'max:100', 'confirmed'],
        ]);

        $user = Auth::user();
        abort_unless($user->role === 'customer', 403);

        $user->name = $data['name'];
        $user->address = $data['address'] ?? null;
        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }
        $user->save();

        return back()->with('status', 'আপনার তথ্য সেভ হয়েছে।');
    }
}
