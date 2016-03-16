<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUniqueIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $collection) {
            $collection->dropIndex('email');
            $collection->index('email', ['sparse' => true, 'unique' => true]);

            $collection->dropIndex('mobile');
            $collection->index('mobile', ['sparse' => true, 'unique' => true]);
        });

        Schema::table('tokens', function (Blueprint $collection) {
            $collection->dropIndex('key');
            $collection->unique('key');
        });

        Schema::table('api_keys', function (Blueprint $collection) {
            $collection->dropIndex('api_key');
            $collection->unique('api_key');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $collection) {
            $collection->dropUnique('email');
            $collection->index('email');

            $collection->dropUnique('mobile');
            $collection->index('mobile');
        });

        Schema::table('tokens', function (Blueprint $collection) {
            $collection->dropUnique('key');
            $collection->index('key');
        });

        Schema::table('api_keys', function (Blueprint $collection) {
            $collection->dropUnique('api_key');
            $collection->index('api_key');
        });
    }
}
