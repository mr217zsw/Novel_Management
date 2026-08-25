<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * 默认管理员账号
 *
 * 账号：13800000000  密码：Admin@123456
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['phone' => '13800000000'],
            [
                'nickname' => '超级管理员',
                'password' => Hash::make('Admin@123456'),
                'is_super_admin' => true,
                'status' => 0,
                'register_channel' => 'web',
            ]
        );

        $this->command->info("默认管理员已创建：13800000000 / Admin@123456");
    }
}
