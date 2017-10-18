<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CrearTablaSisModulos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sis_modulos', function (Blueprint $table) {

            $table->increments('id')->unsigned();
            $table->integer('sis_modulos_id')->unsigned();

            $table->string('nombre');
            $table->string('controlador');
            $table->boolean('es_super');
            $table->string('vista');

            $table->integer('creado_por');
            $table->integer('modificado_por');
            $table->integer('borrado_por');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sis_modulos');
    }
}
