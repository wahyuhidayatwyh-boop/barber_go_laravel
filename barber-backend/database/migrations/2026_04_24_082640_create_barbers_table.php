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
                $table->string('specialty')->nullable();
                $table->decimal('rating', 3, 2)->default(5.00);
                $table->string('image_path')->nullable();
                $table->enum('status', ['active', 'inactive'])->default('active');
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
