<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faculty_id')->constrained()->restrictOnDelete();
            $table->foreignId('option_id')->constrained('options')->restrictOnDelete();
            $table->foreignId('promotion_id')->constrained()->restrictOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();

            $table->unique(['faculty_id', 'option_id', 'promotion_id', 'slug']);
            $table->index(['faculty_id', 'option_id', 'promotion_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
