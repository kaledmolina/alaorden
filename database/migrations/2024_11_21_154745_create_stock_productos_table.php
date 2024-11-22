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
        Schema::create('stock_productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->integer('cantidad_inicial')->default(0);
            $table->integer('cantidad_actual')->default(0);
            $table->integer('cantidad_vendida')->default(0);
            $table->integer('cantidad_advertencia')->default(0);
            $table->integer('cantidad_ajuste')->default(0);
            $table->timestamp('fecha_ultimo_ajuste')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_productos');
    }
};
