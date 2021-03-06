<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToReferenciasTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('referencias', function(Blueprint $table)
		{
			$table->foreign('incidencias_id', 'referencias_ibfk_1')->references('id')->on('incidencias')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('referencias', function(Blueprint $table)
		{
			$table->dropForeign('referencias_ibfk_1');
		});
	}

}
