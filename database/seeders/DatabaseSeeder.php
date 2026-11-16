<?php

namespace Database\Seeders;

use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => 'Administrator',
            'username' => 'admin',
            'email' => 'admin@blogverse.test',
        ]);

        foreach (['penulis1' => 'Penulis Satu', 'penulis2' => 'Penulis Dua'] as $username => $name) {
            $author = User::factory()->create([
                'name' => $name,
                'username' => $username,
                'email' => "{$username}@blogverse.test",
            ]);

            Post::factory()->count(5)->published()->for($author)->create();
            Post::factory()->count(3)->draft()->for($author)->create();
        }
    }
}
