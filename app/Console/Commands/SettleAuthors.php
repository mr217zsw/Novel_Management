<?php

namespace App\Console\Commands;

use App\Services\RevenueShareService;
use Illuminate\Console\Command;

/**
 * 作者分成结算（前一日）
 */
class SettleAuthors extends Command
{
    protected $signature = 'novel:settle-authors {--date= : 结算日期，默认昨日}';

    protected $description = '生成作者分成结算记录';

    public function handle(RevenueShareService $service): int
    {
        $date = $this->option('date') ?: now()->subDay()->toDateString();

        $this->info("开始生成 {$date} 的作者分成");

        $count = $service->generateSettlements($date);

        $this->info("生成分成记录：{$count} 条");

        return self::SUCCESS;
    }
}
