<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;

class MenuDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(MenuSeedTable::class);
        $this->call(MenuChildrenSeedTable::class);
        $this->call(MenuChildSeed00::class);
        $this->call(MenuSeed00::class);
    }
}
