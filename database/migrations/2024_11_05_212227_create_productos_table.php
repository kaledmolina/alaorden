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
            $table->phpEnum('status');
            $table->string('nombre');
            $table->string('code')->unique();
            $table->string('bar_code')->unique()->nullable();            
            $table->string('referencia')->nullable();
            $table->money('precio_compra')->nullable();
            $table->money('precio_venta')->nullable();
            $table->text('descripcion');
            $table->boolean('is_visible')->default(false);
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
