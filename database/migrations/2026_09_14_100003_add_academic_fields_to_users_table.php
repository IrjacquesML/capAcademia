<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('student')->index();
            $table->foreignId('faculty_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('option_id')->nullable()->constrained('options')->restrictOnDelete();
            $table->foreignId('promotion_id')->nullable()->constrained()->restrictOnDelete();

            $table->index(['faculty_id', 'option_id', 'promotion_id']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('faculty_id');
            $table->dropConstrainedForeignId('option_id');
            $table->dropConstrainedForeignId('promotion_id');
            $table->dropColumn('role');
        });
    }
};
