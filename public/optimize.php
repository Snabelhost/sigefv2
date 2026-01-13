<?php
/**
 * Script de Otimização do SIGEF
 * Execute este arquivo via navegador ou CLI para aplicar otimizações
 */

// Bootstrap Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle($request = Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

header('Content-Type: text/html; charset=utf-8');
echo "<h1>🚀 SIGEF - Otimização do Sistema</h1>";
echo "<pre>";

$results = [];

// 1. Limpar cache de views compiladas
echo "1. Limpando views compiladas... ";
$viewsPath = storage_path('framework/views');
$files = glob($viewsPath . '/*.php');
$count = count($files);
foreach ($files as $file) {
    @unlink($file);
}
echo "✓ ($count arquivos removidos)\n";
$results[] = "Views compiladas: $count arquivos removidos";

// 2. Limpar OPcache
echo "2. Limpando OPcache... ";
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✓\n";
    $results[] = "OPcache: Limpo com sucesso";
} else {
    echo "⚠ (OPcache não habilitado)\n";
    $results[] = "OPcache: Não disponível";
}

// 3. Limpar cache de configuração
echo "3. Limpando cache de configuração... ";
Artisan::call('config:clear');
echo "✓\n";
$results[] = "Config cache: Limpo";

// 4. Limpar cache de rotas
echo "4. Limpando cache de rotas... ";
Artisan::call('route:clear');
echo "✓\n";
$results[] = "Route cache: Limpo";

// 5. Limpar cache de aplicação
echo "5. Limpando cache de aplicação... ";
Artisan::call('cache:clear');
echo "✓\n";
$results[] = "Application cache: Limpo";

// 6. Limpar cache de views
echo "6. Limpando cache de views... ";
Artisan::call('view:clear');
echo "✓\n";
$results[] = "View cache: Limpo";

// 7. Gerar cache de configuração para produção
echo "7. Gerando cache de configuração... ";
try {
    Artisan::call('config:cache');
    echo "✓\n";
    $results[] = "Config cache: Gerado para produção";
} catch (Exception $e) {
    echo "⚠ (Erro: " . $e->getMessage() . ")\n";
    $results[] = "Config cache: Erro ao gerar";
}

// 8. Gerar cache de rotas
echo "8. Gerando cache de rotas... ";
try {
    Artisan::call('route:cache');
    echo "✓\n";
    $results[] = "Route cache: Gerado para produção";
} catch (Exception $e) {
    echo "⚠ (Erro: " . $e->getMessage() . ")\n";
    $results[] = "Route cache: Erro ao gerar";
}

// 9. Otimizar autoloader do Composer
echo "9. Verificando autoloader... ";
$composerOptimized = file_exists(base_path('vendor/composer/autoload_classmap.php'));
if ($composerOptimized) {
    echo "✓ (Já otimizado)\n";
} else {
    echo "⚠ (Execute: composer dump-autoload -o)\n";
}
$results[] = "Autoloader: " . ($composerOptimized ? "Otimizado" : "Precisa otimizar");

echo "\n</pre>";

echo "<h2>📊 Resumo das Otimizações</h2>";
echo "<ul>";
foreach ($results as $result) {
    echo "<li>$result</li>";
}
echo "</ul>";

echo "<h2>💡 Recomendações Adicionais</h2>";
echo "<ul>";
echo "<li><strong>Em Produção:</strong> Configure <code>APP_DEBUG=false</code> no .env</li>";
echo "<li><strong>Composer:</strong> Execute <code>composer dump-autoload -o</code> para otimizar o autoloader</li>";
echo "<li><strong>Assets:</strong> Execute <code>npm run build</code> para minificar assets</li>";
echo "<li><strong>Banco de Dados:</strong> Considere adicionar índices nas colunas mais consultadas</li>";
echo "<li><strong>PHP:</strong> Certifique-se de que OPcache está habilitado no php.ini</li>";
echo "</ul>";

echo "<p style='color: green; font-weight: bold;'>✅ Otimização concluída! O sistema deve estar mais rápido agora.</p>";
echo "<p><a href='/admin'>← Voltar ao Painel</a></p>";
