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
        if (!Schema::hasTable('barbershop_settings')) {
            Schema::create('barbershop_settings', function (Blueprint $table) {
                $table->id();
                $table->boolean('is_open')->default(true);
                $table->string('shop_name')->default('CUKURMEN Barbershop');
                $table->string('address')->default('Jl. Merdeka No. 123, Jakarta Pusat');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barbershop_settings');
    }
};

