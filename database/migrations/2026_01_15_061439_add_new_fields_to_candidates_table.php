<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            // Renomear province_id para province (se existir)
            if (Schema::hasColumn('candidates', 'province_id')) {
                $table->renameColumn('province_id', 'province');
            }
            
            // Adicionar campos que faltam
            if (!Schema::hasColumn('candidates', 'municipality')) {
                $table->string('municipality')->nullable()->after('province');
            }
            if (!Schema::hasColumn('candidates', 'address')) {
                $table->text('address')->nullable()->after('municipality');
            }
            if (!Schema::hasColumn('candidates', 'bilhete_identidade')) {
                $table->string('bilhete_identidade')->nullable()->after('photo');
            }
            if (!Schema::hasColumn('candidates', 'certificado_doc')) {
                $table->string('certificado_doc')->nullable()->after('bilhete_identidade');
            }
            if (!Schema::hasColumn('candidates', 'carta_conducao')) {
                $table->string('carta_conducao')->nullable()->after('certificado_doc');
            }
            if (!Schema::hasColumn('candidates', 'passaporte')) {
                $table->string('passaporte')->nullable()->after('carta_conducao');
            }
        });
    }

    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            if (Schema::hasColumn('candidates', 'province')) {
                $table->renameColumn('province', 'province_id');
            }
            
            $columns = ['municipality', 'address', 'bilhete_identidade', 'certificado_doc', 'carta_conducao', 'passaporte'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('candidates', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
