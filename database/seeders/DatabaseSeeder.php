<?php

namespace Database\Seeders;

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
        // \App\Models\User::factory(10)->create();
        //\App\Models\Desk::factory(10)->create();
        $desk=\App\Models\Desk::factory()->count(10)->create();
        $list=\App\Models\DeskList::factory()->count(20)->create();
        $card=\App\Models\Card::factory()->count(40)->create();
        $task=\App\Models\Task::factory()->count(80)->create();
    }
}
