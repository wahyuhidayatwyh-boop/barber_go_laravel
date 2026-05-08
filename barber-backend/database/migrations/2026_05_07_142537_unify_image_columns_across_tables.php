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
        // Add image_path to services
        Schema::table('services', function (Blueprint $table) {
            if (!Schema::hasColumn('services', 'image_path')) {
                $table->string('image_path')->nullable()->after('image_url');
            }
        });

        // Add image_url to barbers
        Schema::table('barbers', function (Blueprint $table) {
            if (!Schema::hasColumn('barbers', 'image_url')) {
                $table->string('image_url')->nullable()->after('image_path');
            }
        });

        // Add image_path to banners
        Schema::table('banners', function (Blueprint $table) {
            if (!Schema::hasColumn('banners', 'image_path')) {
                $table->string('image_path')->nullable()->after('image_url');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });

        Schema::table('barbers', function (Blueprint $table) {
            $table->dropColumn('image_url');
        });

        Schema::table('banners', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });
    }
};
