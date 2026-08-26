<?php

namespace App\Listeners;

use App\Events\UserPaid;
use App\Models\AttributionRecord;

/**
 * 支付后更新归因首付信息
 */
class UpdateAttributionOnPaid
{
    public function handle(UserPaid $event): void
    {
        $record = AttributionRecord::where('user_id', $event->user->id)
            ->whereNull('pay_time')
            ->latest('click_time')
            ->first();

        if ($record) {
            $record->pay_time = now();
            $record->pay_amount = $event->amount;
            $record->save();
        }
    }
}
