<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PackAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Pack_Admin')->insert([
            'name' => "Pack Admin 1",
            'type' => "Duree",
            'capacity' => 70,
            'price' => 20,
            'nbr_days' => 3,
            'nbr_events' => 1
        ]);
        DB::table('Pack_Admin')->insert([
            'name' => "Pack Admin 2",
            'type' => "Event",
            'capacity' => 30,
            'price' => 30,
            'nbr_days' => 3,
            'nbr_events' => 0
        ]);
        DB::table('Pack_Admin')->insert([
            'name' => "Pack Admin 3",
            'type' => "Demo",
            'capacity' => 90,
            'price' => 0,
            'nbr_days' => 5,
            'nbr_events' => 2
        ]);
    }
}
