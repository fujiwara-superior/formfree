<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\WelcomeMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    // ─── 登録画面 ───────────────────────────────────────────
    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:100',
            'name'         => 'required|string|max:50',
            'email'        => 'required|email|unique:users,email',
            'password'     => ['required', 'confirmed', Password::min(8)],
            'agree'        => 'accepted',
        ], [
            'company_name.required' => '会社名を入力してください',
            'name.required'         => '担当者名を入力してください',
            'email.required'        => 'メールアドレスを入力してください',
            'email.unique'          => 'このメールアドレスはすでに登録されています',
            'password.min'          => 'パスワードは8文字以上で設定してください',
            'password.confirmed'    => 'パスワードが一致しません',
            'agree.accepted'        => '利用規約への同意が必要です',
        ]);

        // ① 企業レコード作成
        $companyId = (string) Str::uuid();
        DB::table('companies')->insert([
            'id'                 => $companyId,
            'name'               => $request->company_name,
            'email'              => $request->email,
            'plan'               => 'free',
            'monthly_job_limit'  => 10,
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);

        // ② ユーザーレコード作成
        $userId = (string) Str::uuid();
        DB::table('users')->insert([
            'id'         => $userId,
            'company_id' => $companyId,
            'name'       => $request->name,
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
            'role'       => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ③ ログイン
        Auth::loginUsingId($userId);

        // ④ ウェルカムメール
        $user    = DB::table('users')->where('id', $userId)->first();
        $company = DB::table('companies')->where('id', $companyId)->first();
        try {
            Mail::to($request->email)->send(new \App\Mail\WelcomeMail($user, $company));
        } catch (\Exception $e) {
            logger()->error('Welcome mail failed: ' . $e->getMessage());
        }

        return redirect()->route('dashboard')
            ->with('success', 'ご登録ありがとうございます。まずは最初のPDFを変換してみてください。');
    }

    // ─── ログイン画面 ────────────────────────────────────────
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ], [
            'email.required'    => 'メールアドレスを入力してください',
            'password.required' => 'パスワードを入力してください',
        ]);

        $credentials = $request->only('email', 'password');
        $remember    = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'メールアドレスまたはパスワードが正しくありません']);
    }

    // ─── ログアウト ──────────────────────────────────────────
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    // ─── パスワードリセット（簡易版） ───────────────────────
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // Laravel標準のPassword::sendResetLinkに委譲
        $status = \Illuminate\Support\Facades\Password::sendResetLink(
            $request->only('email')
        );

        if ($status === \Illuminate\Support\Facades\Password::RESET_LINK_SENT) {
            return back()->with('success', 'パスワードリセットのメールを送信しました');
        }

        return back()->withErrors(['email' => 'このメールアドレスは登録されていません']);
    }
}
