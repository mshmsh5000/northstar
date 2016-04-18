<?php

use Illuminate\Database\Migrations\Migration;

class RenameClientTable extends Migration
{
    /**
     * The raw MongoDB interface.
     * @var MongoDB
     */
    protected $mongodb;

    public function __construct()
    {
        $this->mongodb = app('db')->getMongoDB();
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // The 'jenssegers/laravel-mongodb' package doesn't support Schema::rename so...
        $this->mongodb->execute('db.api_keys.renameCollection("clients");');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->mongodb->execute('db.clients.renameCollection("api_keys");');
    }
}
