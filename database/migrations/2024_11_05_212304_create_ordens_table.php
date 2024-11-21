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
            $table->foreignId('vendedor_id')->constrained('vendedors');            
            $table->decimal('total_precio', 10, 2)->nullable();
            $table->timestamps();
        });
        // Migración de la tabla pivote OrdenProducto
        Schema::create('orden_producto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_id')
                ->constrained('ordens')
                ->onDelete('cascade');
            $table->foreignId('producto_id')->constrained('productos');
            $table->string('nombre');
            $table->string('code')->nullable();
            $table->string('bar_code')->nullable();            
            $table->string('referencia')->nullable();
            $table->text('description')->nullable();
            $table->decimal('precio_venta', 10, 2)->nullable();
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
