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
        Schema::create('mnt_producto', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('codigo')->unique();
            $table->unsignedBigInteger('id_establecimiento_origen')->nullable();
            $table->unsignedBigInteger('id_categoria_producto')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();

            $table->foreign('id_establecimiento_origen')->references('id')->on('ctl_establecimiento_origen')
            ->onUpdate('cascade')
            ->onDelete('cascade');

            
            $table->foreign('id_categoria_producto')->references('id')->on('ctl_categoria_producto')
            ->onUpdate('cascade')
            ->onDelete('cascade');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mnt_producto');

    }
};
