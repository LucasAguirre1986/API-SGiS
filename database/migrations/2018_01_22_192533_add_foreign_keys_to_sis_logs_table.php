<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToSisLogsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('sis_logs', function(Blueprint $table)
		{
			$table->foreign('sis_usuarios_id', 'foreignkey_sis_usuario_sis_logs')->references('id')->on('sis_usuarios')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('sis_logs', function(Blueprint $table)
		{
			$table->dropForeign('foreignkey_sis_usuario_sis_logs');
		});
	}

}
