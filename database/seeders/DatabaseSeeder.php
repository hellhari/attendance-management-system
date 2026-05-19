<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // 1. Create the role first safely
        $role = Role::firstOrCreate(
            ['slug' => 'admin'],
            ['name' => 'Administrator']
        );

        // 2. Create the Admin user safely
        $user = User::firstOrCreate(
            ['email' => 'admin@ams.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('admin@ams.com'),
            ]
        );

        // 3. Link the role to the user
        $user->roles()->sync($role->id);
    }
}