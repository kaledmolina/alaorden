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
        Schema::create('ordens', function (Blueprint $table) {
            $table->id();
            $table->string('numero_orden')->unique();
            $table->foreignId('vendedor_id')->constrained('vendedores');
            $table->money('total_precio');
            $table->timestamps();
        });
        // Migración de la tabla pivote OrdenProducto
        Schema::create('orden_producto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_id')->constrained('ordenes');
            $table->foreignId('producto_id')->constrained('productos');
            $table->string('nombre');
            $table->string('code')->unique();
            $table->string('bar_code')->unique()->nullable();            
            $table->string('referencia')->nullable();
            $table->text('description')->nullable();
            $table->money('precio_venta');
            $table->integer('cantidad_asignada');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ordens');
    }
};
