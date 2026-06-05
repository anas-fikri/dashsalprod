<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_history_id')->constrained('import_histories')->onDelete('cascade');
            $table->string('nik');
            $table->string('nama');
            $table->string('company_code'); // NES | NPA
            $table->string('group_name')->nullable();
            $table->string('group_code')->nullable();
            $table->string('sub_department')->nullable();
            $table->string('job_title')->nullable();
            $table->string('structural_status'); // STR1 | STR2 | NSTR
            $table->decimal('basic_salary', 15, 2);
            $table->decimal('gross_salary', 15, 2);
            $table->decimal('net_salary', 15, 2);
            $table->boolean('is_production');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_data');
    }
};
