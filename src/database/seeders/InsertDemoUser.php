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
            'user_name' => 'demouser',
            'password' => bcrypt('bzaBWC54!@#$')
        ]);
    }
}