<?php

use Illuminate\Database\Migrations\Migration;
use Jenssegers\Mongodb\Schema\Blueprint;

class RenameClientFields extends Migration
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
        // Remove the unique index on api_key
        Schema::table('clients', function (Blueprint $collection) {
            $collection->dropIndex('api_key');
        });

        // Rename 'app_id' column to 'client_id'
        $this->mongodb->execute('db.clients.update({}, {$rename:{"app_id":"client_id"}}, { upsert:false, multi:true });');

        // Rename 'api_key' column to 'client_secret'
        $this->mongodb->execute('db.clients.update({}, {$rename:{"api_key":"client_secret"}}, { upsert:false, multi:true });');

        // Add an index for querying by client_id & client_secret
        Schema::table('clients', function (Blueprint $collection) {
            $collection->index(['client_id', 'client_secret']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clients', function (Blueprint $collection) {
            $collection->dropIndex(['client_id', 'client_secret']);
        });

        $this->mongodb->execute(
            'db.clients.update({}, {$rename:{"client_secret":"api_key"}}, { upsert:false, multi:true });'
        );

        $this->mongodb->execute(
            'db.clients.update({}, {$rename:{"client_id":"app_id"}}, { upsert:false, multi:true });'
        );

        Schema::table('clients', function (Blueprint $collection) {
            $collection->unique('api_key');
        });
    }
}
