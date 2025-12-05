<?php
declare(strict_types=1);
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class InsertDemoUser extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Demo User',
            'email' => 'demo@example.com',
            'username' => 'demouser',
            'password' => bcrypt(env('SAMPLE_USER_PASSWORD', 'Password123!')),
        ]);
    }
}