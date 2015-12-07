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

        Token::create([
            'key' => 'S0FyZmlRNmVpMzVsSzJMNUFreEFWa3g0RHBMWlJRd0tiQmhSRUNxWXh6cz0=',
            'user_id' => '5480c950bffebc651c8b456f',
        ]);

        Token::create([
            'key' => 'S0FyZmlRNmVpMzVsSzJMNUFreEFWa3g0RHBMWlJRd0tiQmhSRUNxWXh6cz1=',
            'user_id' => 'bf1039b0271bcc636aa5477c',
        ]);

        Token::create([
            'key' => 'S0FyZmlRNmVpMzVsSzJMNUFreEFWa3g0RHBMWlJRd0tiQmhSRUNxWXh6cz2=',
            'user_id' => 'bf1039b0271bcc636aa5477c',
        ]);
    }
}
