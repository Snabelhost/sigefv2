<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class UnifiedLoginController extends Controller
{
    /**
     * Mostrar o formulário de login unificado.
     */
    public function showLoginForm()
    {
        // Se já estiver autenticado, redirecionar para o painel correto
        if (Auth::check()) {
            return $this->redirectToPanel(Auth::user());
        }

        return view('auth.login');
    }

    /**
     * Processar o login.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required' => 'O email é obrigatório.',
            'email.email' => 'Introduza um email válido.',
            'password.required' => 'A senha é obrigatória.',
        ]);

        // Verificar se o utilizador existe e está ativo
        $user = \App\Models\User::where('email', $credentials['email'])->first();
        
        if ($user && !$user->is_active) {
            throw ValidationException::withMessages([
                'email' => 'A sua conta está desactivada. Contacte o administrador.',
            ]);
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return $this->redirectToPanel(Auth::user());
        }

        throw ValidationException::withMessages([
            'email' => 'As credenciais fornecidas não correspondem aos nossos registos.',
        ]);
    }

    /**
     * Encerrar sessão.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    /**
     * Redirecionar o utilizador para o painel correto baseado no role.
     */
    public function redirectToPanel($user)
    {
        // Prioridade 1: Super Admin
        if ($user->hasRole('super_admin')) {
            return redirect()->intended('/admin');
        }

        // Prioridade 2: Escola (Multi-tenancy)
        if ($user->hasRole('escola_admin') || $user->hasRole('panel_user') || $user->hasRole('escola_user')) {
            if ($user->institution_id) {
                return redirect()->intended('/escola/' . $user->institution_id);
            }
            return redirect()->intended('/escola');
        }

        // Prioridade 3: DPQ
        if ($user->hasRole('dpq_admin') || $user->hasRole('dpq_user')) {
            return redirect()->intended('/dpq');
        }

        // Prioridade 4: Comando
        if ($user->hasRole('comando_admin') || $user->hasRole('comando_user')) {
            return redirect()->intended('/comando');
        }

        // Fallback: Tentar encontrar qualquer painel que o utilizador possa aceder
        $panels = ['admin', 'escola', 'dpq', 'comando'];
        foreach ($panels as $panelId) {
            try {
                $panel = \Filament\Facades\Filament::getPanel($panelId);
                if ($user->canAccessPanel($panel)) {
                    if ($panelId === 'escola' && $user->institution_id) {
                        return redirect()->intended('/escola/' . $user->institution_id);
                    }
                    return redirect()->intended('/' . $panelId);
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        // Se não tiver acesso a nada, logout e erro
        Auth::logout();
        
        return redirect('/login')->withErrors([
            'email' => 'A sua conta não tem permissões para aceder a nenhum painel. Contacte o administrador.',
        ]);
    }
}
