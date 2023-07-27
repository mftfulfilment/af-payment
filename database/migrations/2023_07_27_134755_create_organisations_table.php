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
        Schema::create('organisations', function (Blueprint $table) {
            $table->id();
            $table->string('phone_number')->nullable();
            $table->string('client_name')->nullable();
            $table->string('admin_id')->nullable();
            $table->string('status')->default('inactive'); // inactive, busy, available
            $table->string('sessionId')->nullable();
            $table->string('token')->nullable();
            $table->timestamps();
            $table->softDeletes();
          
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organisations');
    }
};
