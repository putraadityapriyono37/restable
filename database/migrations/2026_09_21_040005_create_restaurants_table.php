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
        Schema::create('restaurants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users');
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->time('open_time');
            $table->time('close_time');
            $table->integer('slot_duration')->default(60); // menit
            $table->enum('dp_type', ['fixed', 'percentage'])->default('percentage');
            $table->decimal('dp_value', 10, 2)->default(30); // 30% atau nominal fixed
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurants');
    }
};
