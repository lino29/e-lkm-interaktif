<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table) {
            if (! Schema::hasColumn('media', 'learning_unit_section_id')) {
                $table->foreignId('learning_unit_section_id')
                    ->nullable()
                    ->after('learning_unit_id')
                    ->constrained('learning_unit_sections')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            if (Schema::hasColumn('media', 'learning_unit_section_id')) {
                $table->dropConstrainedForeignId('learning_unit_section_id');
            }
        });
    }
};
