<?php

/**
 * Aplicar alteração na tabela students para definir valor padrão de enrollment_date
 * Aceder em: http://sigefv2.test/fix-enrollment-date.php
 */

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request = Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "<h1>Corrigir enrollment_date na tabela students</h1>";

try {
    // 1. Atualizar registros existentes que têm enrollment_date NULL
    $updated = DB::statement("UPDATE students SET enrollment_date = CURDATE() WHERE enrollment_date IS NULL");
    echo "<p style='color:green;'>✓ Registros com enrollment_date NULL foram atualizados para a data de hoje.</p>";
    
    // 2. Alterar a coluna para ter um valor padrão
    DB::statement("ALTER TABLE students MODIFY COLUMN enrollment_date DATE DEFAULT (CURDATE())");
    echo "<p style='color:green;'>✓ Coluna enrollment_date alterada para ter valor padrão CURDATE().</p>";
    
    // Verificar a estrutura da coluna
    $columnInfo = DB::select("SHOW COLUMNS FROM students WHERE Field = 'enrollment_date'");
    echo "<h2>Estrutura da coluna após alteração:</h2>";
    echo "<pre>";
    print_r($columnInfo);
    echo "</pre>";
    
    echo "<h2 style='color:green;'>✅ Correção aplicada com sucesso!</h2>";
    echo "<p>Agora você pode criar agentes sem o erro de 'enrollment_date doesn't have a default value'.</p>";
    
} catch (\Exception $e) {
    echo "<p style='color:red;'>❌ Erro: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='/admin/agents'>Admin Agents</a> | <a href='/escola/1/agents'>Escola Agents</a></p>";
