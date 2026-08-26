<?php

namespace App\Services;

use App\Models\Author;
use App\Models\AuthorSettlement;
use App\Models\Book;
use App\Models\Order;

/**
 * 作者分成结算服务
 *
 * 分成模式书籍：扣除平台抽成后，按作者分成比例计算分成。
 * 买断模式：作者无分成。
 */
class RevenueShareService
{
    /**
     * 计算单笔订单的作者分成
     *
     * @param float $amount 订单金额（分）
     * @param Book $book 书籍
     */
    public function calculateAuthorRoyalty(float $amount, Book $book): float
    {
        // 仅分成模式（copyright_type=2 分成）才计算
        if ((int) $book->copyright_type !== 2) {
            return 0;
        }

        // 平台抽成 30%
        $platformFee = $amount * 0.30;
        $remaining = $amount - $platformFee;

        return round($remaining * ($book->royalty_rate / 100), 2);
    }

    /**
     * 生成指定日期的分成结算记录
     */
    public function generateSettlements(string $date): int
    {
        $date = date('Y-m-d', strtotime($date));

        $orders = Order::where('status', Order::STATUS_PAID)
            ->whereDate('pay_time', $date)
            ->with('user')
            ->get();

        $count = 0;

        foreach ($orders as $order) {
            // 仅章节购买涉及作者分成
            if ($order->product_type !== 'chapter') {
                continue;
            }

            $book = Book::find($order->product_id);
            if (!$book || (int) $book->copyright_type !== 2) {
                continue;
            }

            $royalty = $this->calculateAuthorRoyalty($order->pay_amount, $book);
            if ($royalty <= 0) {
                continue;
            }

            AuthorSettlement::create([
                'author_id' => $book->author_id,
                'order_id' => $order->id,
                'amount' => $royalty,
                'settle_date' => $date,
                'status' => AuthorSettlement::STATUS_PENDING,
            ]);

            $count++;
        }

        return $count;
    }
}
