<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Category;
use App\Models\Channel;
use App\Models\Chapter;
use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * 演示数据
 *
 * 生成基础分类、渠道、充值商品、示例书籍（用于本地开发联调）。
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // 分类
        $categories = ['都市', '玄幻', '仙侠', '言情', '悬疑', '历史'];
        foreach ($categories as $name) {
            Category::firstOrCreate(['name' => $name], ['status' => 1, 'sort' => 0]);
        }

        // 渠道
        $channels = [
            ['name' => '抖音', 'code' => 'douyin', 'status' => 1],
            ['name' => '微信', 'code' => 'wechat', 'status' => 1],
            ['name' => '快手', 'code' => 'kuaishou', 'status' => 1],
        ];
        foreach ($channels as $c) {
            Channel::firstOrCreate(['code' => $c['code']], $c);
        }

        // 充值商品
        $recharges = [
            ['name' => '100阅读币', 'product_type' => 'recharge', 'price' => 100, 'coin_amount' => 100, 'sort' => 1],
            ['name' => '500阅读币', 'product_type' => 'recharge', 'price' => 480, 'coin_amount' => 500, 'sort' => 2],
            ['name' => '1000阅读币', 'product_type' => 'recharge', 'price' => 900, 'coin_amount' => 1000, 'sort' => 3],
        ];
        foreach ($recharges as $p) {
            Product::firstOrCreate(['name' => $p['name']], array_merge($p, ['is_active' => 1]));
        }

        // VIP 商品
        Product::firstOrCreate(
            ['name' => '月卡VIP', 'product_type' => 'vip'],
            ['price' => 3000, 'vip_days' => 30, 'is_active' => 1, 'sort' => 1]
        );

        // 示例书籍（含章节）
        if (Book::count() === 0) {
            $categoryId = Category::where('name', '都市')->value('id');
            $book = Book::create([
                'title' => '都市异能：我的系统有点强',
                'category_id' => $categoryId,
                'description' => '一个普通青年的都市逆袭之路。',
                'tags' => ['都市', '系统', '爽文'],
                'copyright_type' => 2,
                'royalty_rate' => 50,
                'min_price' => 10,
                'status' => 2,
                'audit_status' => 1,
                'published_at' => now(),
                'total_chapters' => 5,
                'total_words' => 25000,
            ]);

            for ($i = 1; $i <= 5; $i++) {
                Chapter::create([
                    'novel_id' => $book->id,
                    'chapter_no' => $i,
                    'title' => "第{$i}章",
                    'content_oss_key' => "chapters/{$book->id}/{$i}.txt",
                    'word_count' => 5000,
                    'is_free' => $i <= 2 ? 1 : 0,
                    'price' => $i <= 2 ? 0 : 10,
                    'status' => 1,
                    'audit_status' => 1,
                ]);
            }
        }
    }
}
