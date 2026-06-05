<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_histories', function (Blueprint $table) {
            $table->id();
            $table->string('file_name');
            $table->string('period', 7); // YYYY-MM
            $table->string('type'); // payroll | operational
            $table->foreignId('imported_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_histories');
    }
};
