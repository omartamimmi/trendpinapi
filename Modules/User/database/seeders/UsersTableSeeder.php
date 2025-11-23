<?php
namespace Modules\User\database\seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

       DB::table('users')->insert([
           'name' => 'Admin',
           'email' => 'admin@pinpoint.com',
           'password' => bcrypt('123123123'),
           'created_at' =>  date("Y-m-d H:i:s"),
       ]);
       $user = User::where('email','admin@pinpoint.com')->first();
       $user->assignRole('admin');

       DB::table('users')->insert([
           'name' => 'vendor',
           'email' => 'host@pinpoint.com',
           'password' => bcrypt('123123123'),
           'created_at' =>  date("Y-m-d H:i:s"),
       ]);
       $user = User::where('email','host@pinpoint.com')->first();
       $user->assignRole('host');

       DB::table('users')->insert([
           'name' => 'Customer',
           'email' => 'customer@pinpoint.com',
           'password' => bcrypt('123123123'),
           'created_at' =>  date("Y-m-d H:i:s"),
       ]);
       $user = User::where('email','customer@pinpoint.com')->first();
       $user->assignRole('customer');


    }
}
