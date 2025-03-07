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
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('username');
            $table->string('name');
            $table->string('email');
            $table->string('image')->nullable();
            $table->string('phone');
            $table->date('date_of_birth');
            $table->string('address');
            $table->string('gender');
            $table->foreignId('education_id')->nullable()->constrained('educations')->onDelete('set null');
            $table->foreignId('disability_id')->nullable()->constrained('disabilities')->onDelete('set null');
            $table->string('cv')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
