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
        Schema::table('learning_unit_sections', function (Blueprint $table) {
            if (! Schema::hasColumn('learning_unit_sections', 'editor_type')) {
                $table->string('editor_type')->default('rich_text')->after('section_type');
            }

            if (! Schema::hasColumn('learning_unit_sections', 'settings')) {
                $table->json('settings')->nullable()->after('content_json');
            }

            if (! Schema::hasColumn('learning_unit_sections', 'is_locked')) {
                $table->boolean('is_locked')->default(false)->after('is_required');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('learning_unit_sections', function (Blueprint $table) {
            if (Schema::hasColumn('learning_unit_sections', 'is_locked')) {
                $table->dropColumn('is_locked');
            }

            if (Schema::hasColumn('learning_unit_sections', 'settings')) {
                $table->dropColumn('settings');
            }

            if (Schema::hasColumn('learning_unit_sections', 'editor_type')) {
                $table->dropColumn('editor_type');
            }
        });
    }
};
