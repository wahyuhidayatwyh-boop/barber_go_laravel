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
        if (! Schema::hasTable('barbers')) {
            Schema::create('barbers', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->decimal('rating', 2, 1)->default(0.0);
                $table->string('image_url')->nullable();
                $table->string('experience')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barbers');
    }
};
