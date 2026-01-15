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
     * Obter a lista de painéis que o utilizador pode aceder.
     */
    protected function getAccessiblePanels($user): array
    {
        $accessiblePanels = [];
        $panels = [
            'admin' => ['name' => 'Administração', 'icon' => 'heroicon-o-cog-6-tooth', 'url' => '/admin'],
            'escola' => ['name' => 'Escola', 'icon' => 'heroicon-o-academic-cap', 'url' => $user->institution_id ? '/escola/' . $user->institution_id : '/escola'],
            'dpq' => ['name' => 'DPQ', 'icon' => 'heroicon-o-building-office', 'url' => '/dpq'],
            'comando' => ['name' => 'Comando', 'icon' => 'heroicon-o-shield-check', 'url' => '/comando'],
        ];

        foreach ($panels as $panelId => $panelInfo) {
            try {
                $panel = \Filament\Facades\Filament::getPanel($panelId);
                if ($user->canAccessPanel($panel)) {
                    $accessiblePanels[$panelId] = $panelInfo;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return $accessiblePanels;
    }

    /**
     * Redirecionar o utilizador para o painel correto baseado no role.
     */
    public function redirectToPanel($user)
    {
        // Limpar sessão intended para evitar redirecionamentos para URLs antigas
        session()->forget('url.intended');
        
        // Obter painéis acessíveis
        $accessiblePanels = $this->getAccessiblePanels($user);
        
        // Se o utilizador tem acesso a mais de um painel, mostrar página de selecção
        if (count($accessiblePanels) > 1) {
            return redirect('/select-panel');
        }
        
        // Se tem acesso a apenas um painel, redirecionar diretamente
        if (count($accessiblePanels) === 1) {
            $panel = array_values($accessiblePanels)[0];
            return redirect($panel['url']);
        }

        // Se não tiver acesso a nada, logout e erro
        Auth::logout();
        
        return redirect('/login')->withErrors([
            'email' => 'A sua conta não tem permissões para aceder a nenhum painel. Contacte o administrador.',
        ]);
    }

    /**
     * Mostrar página de selecção de painéis.
     */
    public function showPanelSelection()
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user();
        $accessiblePanels = $this->getAccessiblePanels($user);

        // Se só tem 1 painel, redirecionar
        if (count($accessiblePanels) <= 1) {
            return $this->redirectToPanel($user);
        }

        return view('auth.select-panel', ['panels' => $accessiblePanels, 'user' => $user]);
    }
}
