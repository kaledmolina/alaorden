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
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categoria_id')->constrained('categorias');
            $table->enum('status', ['activo', 'inactivo', 'pendiente'])->default('activo');
            $table->string('nombre')->unique();
            $table->string('code')->unique()->nullable();
            $table->string('bar_code')->unique()->nullable();            
            $table->string('referencia')->unique()->nullable(); 
            $table->decimal('precio_compra', 10, 2)->nullable();
            $table->decimal('precio_venta', 10, 2)->nullable();
            $table->decimal('comision', 10, 2)->nullable();            
            $table->text('descripcion');
            $table->boolean('is_visible')->default(true);
            $table->boolean('is_activo')->default(true);            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
