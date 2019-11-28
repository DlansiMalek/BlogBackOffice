<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Created by IntelliJ IDEA.
 * User: ABBES
 * Date: 02/09/2019
 * Time: 11:58
 */
class MigrationOldData extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $pathDB = public_path('db/migration_old_data.sql');

        DB::unprepared(file_get_contents($pathDB));

        $this->command->info('Migration old data seeded!');
    }
}