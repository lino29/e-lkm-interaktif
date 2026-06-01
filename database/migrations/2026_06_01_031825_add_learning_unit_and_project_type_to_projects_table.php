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
        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('learning_unit_id')
                ->nullable()
                ->after('module_id')
                ->constrained()
                ->nullOnDelete();

            $table->string('project_type')
                ->nullable()
                ->after('project_title');

            $table->longText('data_to_collect')
                ->nullable()
                ->after('collected_data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropConstrainedForeignId('learning_unit_id');
            $table->dropColumn(['project_type', 'data_to_collect']);
        });
    }
};
