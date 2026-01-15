<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            // Adicionar campos de documentos se não existirem
            if (!Schema::hasColumn('candidates', 'curriculum')) {
                $table->string('curriculum')->nullable()->after('certificado_doc');
            }
            if (!Schema::hasColumn('candidates', 'registro_criminal')) {
                $table->string('registro_criminal')->nullable()->after('curriculum');
            }
            
            // Adicionar province_id e municipality_id
            if (!Schema::hasColumn('candidates', 'province_id')) {
                $table->foreignId('province_id')->nullable()->after('section')->constrained('provinces')->nullOnDelete();
            }
            if (!Schema::hasColumn('candidates', 'municipality_id')) {
                $table->foreignId('municipality_id')->nullable()->after('province_id')->constrained('municipalities')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            if (Schema::hasColumn('candidates', 'curriculum')) {
                $table->dropColumn('curriculum');
            }
            if (Schema::hasColumn('candidates', 'registro_criminal')) {
                $table->dropColumn('registro_criminal');
            }
            if (Schema::hasColumn('candidates', 'municipality_id')) {
                $table->dropForeign(['municipality_id']);
                $table->dropColumn('municipality_id');
            }
            if (Schema::hasColumn('candidates', 'province_id')) {
                $table->dropForeign(['province_id']);
                $table->dropColumn('province_id');
            }
        });
    }
};
