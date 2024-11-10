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
        Schema::create('mnt_documento_certificacion', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_archivo');
            $table->string('ruta_documento_certificacion');
            $table->unsignedBigInteger('id_producto')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();

            $table->foreign('id_producto')->references('id')->on('mnt_producto')
            ->onUpdate('cascade')
            ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mnt_documento_certificacion');
    }
};
