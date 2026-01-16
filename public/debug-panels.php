<?php
/**
 * Debug de painéis - Aceda via http://sigefv2.test/debug-panels.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Obter utilizador autenticado
$user = \Illuminate\Support\Facades\Auth::user();

header('Content-Type: text/html; charset=utf-8');
echo '<html><head><title>Debug Painéis</title>';
echo '<style>body{font-family:Arial;padding:20px;background:#1a1a2e;color:#eee;}';
echo 'table{border-collapse:collapse;width:100%;margin:20px 0;}';
echo 'th,td{border:1px solid #4a5568;padding:10px;text-align:left;}';
echo 'th{background:#2d3748;}.yes{color:#4ade80;}.no{color:#f87171;}</style></head><body>';

if (!$user) {
    echo '<h1>❌ Utilizador não autenticado</h1>';
    echo '<p>Faça login primeiro em <a href="/login" style="color:#60a5fa;">/login</a></p>';
    echo '</body></html>';
    exit;
}

echo '<h1>🔍 Debug de Painéis - SIGEF</h1>';

// Info do utilizador
echo '<h2>👤 Utilizador</h2>';
echo '<table>';
echo '<tr><th>Campo</th><th>Valor</th></tr>';
echo '<tr><td>ID</td><td>' . $user->id . '</td></tr>';
echo '<tr><td>Nome</td><td>' . $user->name . '</td></tr>';
echo '<tr><td>Email</td><td>' . $user->email . '</td></tr>';
echo '<tr><td>institution_id</td><td>' . ($user->institution_id ?? 'NULL') . '</td></tr>';
echo '</table>';

// Roles do utilizador
echo '<h2>🔑 Roles</h2>';
$roles = $user->getRoleNames()->toArray();
echo '<table>';
echo '<tr><th>Role</th></tr>';
if (empty($roles)) {
    echo '<tr><td class="no">Nenhum role atribuído</td></tr>';
} else {
    foreach ($roles as $role) {
        echo '<tr><td>' . $role . '</td></tr>';
    }
}
echo '</table>';

// Painéis e acesso
echo '<h2>📋 Painéis</h2>';
$panels = ['admin', 'escola', 'dpq', 'comando'];
echo '<table>';
echo '<tr><th>Painel</th><th>canAccessPanel()</th><th>Condição Específica</th></tr>';

foreach ($panels as $panelId) {
    try {
        $panel = \Filament\Facades\Filament::getPanel($panelId);
        $canAccess = $user->canAccessPanel($panel);
        $canAccessHtml = $canAccess ? '<span class="yes">✅ SIM</span>' : '<span class="no">❌ NÃO</span>';
        
        // Verificar condição específica
        $specificAccess = false;
        $reason = '';
        
        if ($panelId === 'admin') {
            if ($user->hasRole('super_admin') || $user->hasRole('admin') || $user->hasRole('panel_user') || $user->hasRole('admin_admin')) {
                $specificAccess = true;
                $reason = 'Tem role admin/*';
            }
        } elseif ($panelId === 'escola') {
            if (($user->hasRole('escola_admin') || $user->hasRole('escola_user')) && $user->institution_id) {
                $specificAccess = true;
                $reason = 'Tem role escola_* + institution_id';
            } elseif ($user->hasRole('escola_admin') || $user->hasRole('escola_user')) {
                $reason = 'Tem role escola_* mas SEM institution_id';
            }
        } elseif ($panelId === 'dpq') {
            if ($user->hasRole('dpq_admin') || $user->hasRole('dpq_user')) {
                $specificAccess = true;
                $reason = 'Tem role dpq_* (não precisa institution_id)';
            }
        } elseif ($panelId === 'comando') {
            if ($user->hasRole('comando_admin') || $user->hasRole('comando_user')) {
                $specificAccess = true;
                $reason = 'Tem role comando_* (não precisa institution_id)';
            }
        }
        
        $specificHtml = $specificAccess ? '<span class="yes">✅ SIM - ' . $reason . '</span>' : '<span class="no">❌ NÃO - ' . $reason . '</span>';
        
        echo '<tr><td>' . strtoupper($panelId) . '</td><td>' . $canAccessHtml . '</td><td>' . $specificHtml . '</td></tr>';
    } catch (\Exception $e) {
        echo '<tr><td>' . strtoupper($panelId) . '</td><td colspan="2" class="no">Erro: ' . $e->getMessage() . '</td></tr>';
    }
}
echo '</table>';

// Resultado
echo '<h2>🎯 Resultado do Redirect</h2>';
$controller = new \App\Http\Controllers\Auth\UnifiedLoginController();
$method = new \ReflectionMethod($controller, 'getAccessiblePanels');
$method->setAccessible(true);
$accessiblePanels = $method->invoke($controller, $user);

echo '<p><strong>Painéis acessíveis calculados:</strong> ' . count($accessiblePanels) . '</p>';
echo '<table>';
echo '<tr><th>Painel</th><th>URL</th></tr>';
foreach ($accessiblePanels as $id => $info) {
    echo '<tr><td>' . strtoupper($id) . '</td><td>' . $info['url'] . '</td></tr>';
}
echo '</table>';

if (count($accessiblePanels) > 1) {
    echo '<p class="yes">✅ Deveria mostrar página de seleção</p>';
} elseif (count($accessiblePanels) === 1) {
    $first = array_values($accessiblePanels)[0];
    echo '<p class="no">⚠️ Redireciona direto para: ' . $first['url'] . '</p>';
} else {
    echo '<p class="no">❌ Nenhum painel acessível - fará logout</p>';
}

echo '<br><a href="/admin" style="color:#818cf8;text-decoration:none;padding:10px 20px;background:#16213e;border-radius:8px;">← Voltar ao Admin</a>';
echo '</body></html>';
