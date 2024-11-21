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
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_id')->constrained('ordens');
            $table->foreignId('vendedor_id')->constrained('vendedors');
            $table->enum('status', ['pendiente', 'reparto', 'cancelada', 'completada'])->default('reparto');
            $table->decimal('total_precio', 10, 2)->nullable();
            $table->decimal('profit_vendedor', 10, 2)->nullable();
            $table->timestamps();
        });
        // Migración de la tabla pivote VentaProducto
        Schema::create('venta_producto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->constrained('ventas');
            $table->foreignId('producto_id')->constrained('productos');
            $table->string('nombre');
            $table->string('code')->nullable();
            $table->string('bar_code')->nullable();            
            $table->string('referencia')->nullable();
            $table->text('description')->nullable();
            $table->decimal('precio_venta', 10, 2)->nullable();
            $table->integer('comision')->nullable();
            $table->integer('cantidad_vendida');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
