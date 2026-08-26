<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

/**
 * 订单管理（财务）
 */
class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('user:id,nickname');

        if ($request->filled('order_no')) {
            $query->where('order_no', 'like', '%' . $request->input('order_no') . '%');
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('platform')) {
            $query->where('platform', $request->input('platform'));
        }
        if ($request->filled('product_type')) {
            $query->where('product_type', $request->input('product_type'));
        }
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->input('date'));
        }

        return response()->success($query->orderByDesc('id')->paginate($request->input('per_page', 20)));
    }

    public function show(int $id)
    {
        return response()->success(Order::with('user:id,nickname')->findOrFail($id));
    }

    /**
     * 对账统计
     *
     * GET /api/admin/orders/reconcile?date=
     */
    public function reconcile(Request $request)
    {
        $date = $request->input('date', now()->toDateString());

        $orders = Order::whereDate('created_at', $date);

        return response()->success([
            'date' => $date,
            'total_orders' => (clone $orders)->count(),
            'paid_orders' => (clone $orders)->where('status', Order::STATUS_PAID)->count(),
            'pending_orders' => (clone $orders)->where('status', Order::STATUS_PENDING)->count(),
            'refunded_amount' => (float) (clone $orders)->where('status', Order::STATUS_REFUNDED)->sum('pay_amount'),
            'paid_amount' => (float) (clone $orders)->where('status', Order::STATUS_PAID)->sum('pay_amount'),
        ]);
    }

    /**
     * 退款
     *
     * POST /api/admin/orders/{id}/refund
     */
    public function refund(Request $request, int $id)
    {
        $order = Order::findOrFail($id);
        if ($order->status !== Order::STATUS_PAID) {
            return response()->fail('仅已支付订单可退款', 400);
        }

        $gateway = app(\App\Services\Payment\PaymentRouter::class)->route($order->platform);
        $gateway->refund($order->order_no, $order->pay_amount);

        $order->status = Order::STATUS_REFUNDED;
        $order->save();

        return response()->success($order, '退款已发起');
    }
}
