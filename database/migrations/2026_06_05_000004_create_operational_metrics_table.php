<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operational_metrics', function (Blueprint $table) {
            $table->id();
            $table->string('period', 7)->unique(); // YYYY-MM
            $table->decimal('total_sales', 15, 2);
            $table->decimal('production_volume', 15, 2);
            $table->decimal('production_cost', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operational_metrics');
    }
};
