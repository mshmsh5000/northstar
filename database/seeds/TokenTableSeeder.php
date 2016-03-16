<?php

use Illuminate\Database\Seeder;
use Northstar\Models\Token;

class TokenTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('tokens')->delete();
    }
}
