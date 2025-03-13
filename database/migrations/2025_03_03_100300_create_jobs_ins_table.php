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
        Schema::create('jobs_ins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->double('salary');
            $table->string('location');
            $table->foreignId('company_name')->constrained('companies')->onDelete('cascade');
            $table->foreignId('disability_type')->nullable()->constrained('disabilities')->onDelete('set null');
            $table->foreignId('education_level')->nullable()->constrained('educations')->onDelete('set null');
            $table->foreignId('experience_duration')->nullable()->constrained('experiences')->onDelete('set null');
            $table->foreignId('type_duration')->nullable()->constrained('types')->onDelete('set null');
            $table->foreignId('policy_location')->nullable()->constrained('policies')->onDelete('set null');
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobs_ins');
    }
};
