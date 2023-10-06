<?php

namespace Database\Seeders;


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // User::factory(10)->create();

        $faker = \Faker\Factory::create();

        DB::table("users")->insert([
            "name" => 'admin',
            "email" => 'admin@admin.com',
            "password" => Hash::make('password')

        ]);

        DB::table("users")->insert([
            "name" => 'editor',
            "email" => 'editor@editor.com',
            "password" => Hash::make('password')

        ]);

        $client1 = DB::table("clients")->insertGetId([
            "name" => 'carparts',
            "email" => 'carparts@carparts.com',
            "password" => Hash::make('password'),
            'client_id' => Str::uuid()->toString(),
            'client_secret' => Str::uuid()->toString()
        ]);

        $shipper1 = DB::table("shippers")->insertGetId([
            "name" => 'FEDEX',
            "method_name" => 'FDX'
        ]);


        DB::table("shippers")->insert([
            "name" => 'LSO',
            "method_name" => 'LSO'
        ]);

        DB::table("shippers")->insert([
            "name" => 'USPS',
            "method_name" => 'USPS'
        ]);

        DB::table("shippers")->insert([
            "name" => 'UPS',
            "method_name" => 'UPS'
        ]);

        DB::table("tracking_queues")->insert([
            "client_id" => $client1,
            "shipper_id" => $shipper1,
            'tracking_number' => rand(1000,9000)
        ]);







    }
}
