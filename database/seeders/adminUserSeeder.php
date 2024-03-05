<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class adminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = new User();
        $user->agency_id = 0;
        $user->name = "Admin";
        $user->email = "arsh@code4each.com";
        $user->role = "super_admin";
        $user->password =  bcrypt('038r7l1i');
        $user->email_verified_at = Carbon::now();
        $user->phone = "8054857702";
        $user->save();
    }
}
