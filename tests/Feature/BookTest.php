<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_book_list(): void
    {
        $category = Category::create(['name' => '都市', 'status' => 1, 'sort' => 0]);

        Book::create([
            'title' => '测试小说',
            'category_id' => $category->id,
            'description' => '简介',
            'copyright_type' => 2,
            'royalty_rate' => 50,
            'status' => 2,
            'audit_status' => 1,
            'published_at' => now(),
        ]);

        $response = $this->getJson('/api/v1/books');

        $response->assertOk()->assertJson(['code' => 0]);
        $this->assertCount(1, $response->json('data.data'));
    }

    public function test_draft_book_not_in_list(): void
    {
        Book::create([
            'title' => '草稿小说',
            'copyright_type' => 1,
            'status' => 0, // 草稿
            'audit_status' => 0,
        ]);

        $this->getJson('/api/v1/books')
            ->assertOk()
            ->assertJsonCount(0, 'data.data');
    }
}
