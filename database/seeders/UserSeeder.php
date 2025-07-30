<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        User::factory()->create([
            'username' => 'PiotrKowalski',
            'name' => 'Piotr',
            'surname' => 'Kowalski',
            'sex' => 'male',
            'birth_date' => '1983-04-12',
            // 'password' => '$2y$12$CUy3WxzmUbcoH0smfsx.reeMis76HW1gn9oAaf4HiCUFdBcA41y56' //1983-04-12
        ]);

        User::factory()->create([
            'username' => 'AnnaJablonska',
            'name' => 'Anna',
            'surname' => 'Jabłońska',
            'sex' => 'female',
            'birth_date' => '2002-12-12',
            //  'password' => '$2y$12$0v9/dNz5kRPP8DCM1k3rwOGwNqgEs8T/VRO8cT/nfoyZ1okD18b9O' //2002-12-12
        ]);

        User::factory()->create([
            'username' => 'AndrzejKowalski',
            'name' => 'Andrzej',
            'surname' => 'Kowalski',
            'sex' => 'male',
            'birth_date' => '2020-01-31',
            //  'password' => '$2y$12$agzJKnQbPLayPLCXfXauGOGe7xMkdQAvWI8XcJN.M7586dRZv7iWC' //2020-01-31
        ]);

        User::factory()->create([
            'username' => 'BozenaWisniewska',
            'name' => 'Bożena',
            'surname' => 'Wiśniewska',
            'sex' => 'male',
            'birth_date' => '1980-12-23',
            //  'password' => '$2y$12$M3W3NHKhWhBu83cG0T2Uq.J/HkQg3S2XnsgwIpscaZUwBTpOJOFAC' //1980-12-23
        ]);
    }
}
