<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $imageUrl = "https://picsum.photos/seed/{seed}/300/300";

        // Admin
        User::factory()->create([
            'name'      => config('admin.name'),
            'email'     => config('admin.email'),
            'password'  => Hash::make(config('admin.password')),
            'avatar'    => str_replace("{seed}", fake()->uuid(), $imageUrl),
        ]);
        // Blogger
        User::factory()->create([
            'name'      => 'blogger',
            'email'     => 'blogger@example.net',
            'password'  => Hash::make('12345678'),
            'avatar'    => str_replace("{seed}", fake()->uuid(), $imageUrl),
        ]);
        // Other users
        User::factory(10)->create();
    }
}
