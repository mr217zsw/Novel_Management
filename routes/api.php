<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// ==================== 公开接口（无需登录） ====================

Route::prefix('v1')->group(function () {

    // ---- 认证 ----
    Route::prefix('auth')->group(function () {
        Route::post('login', [App\Http\Controllers\Api\AuthController::class, 'login'])->middleware('throttle:auth');
        Route::post('login/phone', [App\Http\Controllers\Api\AuthController::class, 'loginByPhone'])->middleware('throttle:auth');
        Route::post('sms-code', [App\Http\Controllers\Api\AuthController::class, 'sendSmsCode'])->middleware('throttle:auth');
    });

    // ---- 书籍（公开） ----
    Route::get('books', [App\Http\Controllers\Api\BookController::class, 'index'])->middleware('throttle:public');
    Route::get('books/{id}', [App\Http\Controllers\Api\BookController::class, 'show']);
    Route::get('books/{id}/chapters', [App\Http\Controllers\Api\BookController::class, 'chapters']);

    // ---- 评论列表（公开） ----
    Route::get('comments', [App\Http\Controllers\Api\CommentController::class, 'index']);

    // ---- 支付回调（公开，签名验证） ----
    Route::post('payment/callback/{platform}', [App\Http\Controllers\Api\PaymentController::class, 'callback']);
    Route::post('payment/verify', [App\Http\Controllers\Api\PaymentController::class, 'verifyApple']);

    // ---- 归因点击落地（公开，记录广告点击） ----
    Route::post('attribution/click', [App\Http\Controllers\Api\AttributionController::class, 'recordClick']);

    // ---- 需要登录 ----
    Route::middleware('jwt.auth')->group(function () {
        Route::post('auth/refresh', [App\Http\Controllers\Api\AuthController::class, 'refresh']);
        Route::post('auth/logout', [App\Http\Controllers\Api\AuthController::class, 'logout']);
        Route::get('auth/me', [App\Http\Controllers\Api\AuthController::class, 'me']);

        // OSS 直传
        Route::get('oss/sts-token', [App\Http\Controllers\Api\OssController::class, 'stsToken']);
        Route::post('oss/complete', [App\Http\Controllers\Api\OssController::class, 'complete']);
        Route::post('oss/chapter', [App\Http\Controllers\Api\OssController::class, 'chapter']);

        // 章节
        Route::get('chapters/{id}', [App\Http\Controllers\Api\ChapterController::class, 'show']);
        Route::post('chapters/{id}/progress', [App\Http\Controllers\Api\ChapterController::class, 'progress']);

        // 评论
        Route::post('comments', [App\Http\Controllers\Api\CommentController::class, 'store']);
        Route::post('comments/{id}/like', [App\Http\Controllers\Api\CommentController::class, 'like']);

        // 支付
        Route::post('payment/orders', [App\Http\Controllers\Api\PaymentController::class, 'createOrder']);
        Route::get('payment/orders/{order_no}', [App\Http\Controllers\Api\PaymentController::class, 'query']);

        // 广告奖励（IAA）
        Route::post('ad/reward', [App\Http\Controllers\Api\AdController::class, 'reward']);

        // 用户中心
        Route::get('user/profile', [App\Http\Controllers\Api\UserController::class, 'profile']);
        Route::put('user/profile', [App\Http\Controllers\Api\UserController::class, 'updateProfile']);
        Route::get('user/shelf', [App\Http\Controllers\Api\UserController::class, 'shelf']);
        Route::get('user/products', [App\Http\Controllers\Api\UserController::class, 'products']);
    });
});

// ==================== 管理后台接口 ====================

Route::prefix('admin')->middleware('admin.auth')->group(function () {

    // 后台登录（公开）
    Route::post('login', [App\Http\Controllers\Api\AuthController::class, 'adminLogin']);

    // ---- 投放运营域 ----
    Route::get('channels', [App\Http\Controllers\Admin\ChannelController::class, 'index']);
    Route::post('channels', [App\Http\Controllers\Admin\ChannelController::class, 'store'])->middleware('permission:channel.create');
    Route::put('channels/{id}', [App\Http\Controllers\Admin\ChannelController::class, 'update'])->middleware('permission:channel.update');
    Route::delete('channels/{id}', [App\Http\Controllers\Admin\ChannelController::class, 'destroy'])->middleware('permission:channel.delete');

    Route::get('campaigns', [App\Http\Controllers\Admin\CampaignController::class, 'index']);
    Route::post('campaigns', [App\Http\Controllers\Admin\CampaignController::class, 'store'])->middleware('permission:campaign.create');
    Route::put('campaigns/{id}', [App\Http\Controllers\Admin\CampaignController::class, 'update'])->middleware('permission:campaign.update');
    Route::delete('campaigns/{id}', [App\Http\Controllers\Admin\CampaignController::class, 'destroy'])->middleware('permission:campaign.delete');
    Route::post('campaigns/{id}/toggle', [App\Http\Controllers\Admin\CampaignController::class, 'toggle'])->middleware('permission:campaign.update');

    Route::get('materials', [App\Http\Controllers\Admin\MaterialController::class, 'index']);
    Route::post('materials', [App\Http\Controllers\Admin\MaterialController::class, 'store'])->middleware('permission:material.create');
    Route::post('materials/{id}/audit', [App\Http\Controllers\Admin\MaterialController::class, 'audit'])->middleware('permission:material.audit');
    Route::delete('materials/{id}', [App\Http\Controllers\Admin\MaterialController::class, 'destroy'])->middleware('permission:material.delete');

    Route::get('attributions', [App\Http\Controllers\Admin\AttributionController::class, 'index']);
    Route::get('attributions/roi', [App\Http\Controllers\Admin\AttributionController::class, 'roi']);
    Route::get('attributions/book/{bookId}', [App\Http\Controllers\Admin\AttributionController::class, 'bookRoi']);

    // ---- 内容版权域 ----
    Route::get('books', [App\Http\Controllers\Admin\BookController::class, 'index']);
    Route::post('books', [App\Http\Controllers\Admin\BookController::class, 'store'])->middleware('permission:novel.create');
    Route::get('books/{id}', [App\Http\Controllers\Admin\BookController::class, 'show']);
    Route::put('books/{id}', [App\Http\Controllers\Admin\BookController::class, 'update'])->middleware('permission:novel.update');
    Route::delete('books/{id}', [App\Http\Controllers\Admin\BookController::class, 'destroy'])->middleware('permission:novel.delete');
    Route::post('books/{id}/audit', [App\Http\Controllers\Admin\BookController::class, 'audit'])->middleware('permission:novel.audit');
    Route::post('books/{id}/toggle', [App\Http\Controllers\Admin\BookController::class, 'toggle']);

    Route::get('books/{bookId}/chapters', [App\Http\Controllers\Admin\ChapterController::class, 'index']);
    Route::post('books/{bookId}/chapters', [App\Http\Controllers\Admin\ChapterController::class, 'store'])->middleware('permission:chapter.create');
    Route::post('chapters/{id}/audit', [App\Http\Controllers\Admin\ChapterController::class, 'audit'])->middleware('permission:chapter.audit');

    Route::get('copyrights', [App\Http\Controllers\Admin\CopyrightController::class, 'index']);
    Route::post('copyrights', [App\Http\Controllers\Admin\CopyrightController::class, 'store'])->middleware('permission:copyright.create');
    Route::post('copyrights/{id}/pay', [App\Http\Controllers\Admin\CopyrightController::class, 'pay'])->middleware('permission:copyright.pay');

    Route::get('authors', [App\Http\Controllers\Admin\AuthorController::class, 'index']);
    Route::post('authors', [App\Http\Controllers\Admin\AuthorController::class, 'store'])->middleware('permission:author.create');
    Route::put('authors/{id}', [App\Http\Controllers\Admin\AuthorController::class, 'update'])->middleware('permission:author.update');
    Route::post('authors/{id}/contract', [App\Http\Controllers\Admin\AuthorController::class, 'contract'])->middleware('permission:author.update');
    Route::get('authors/{id}/settlements', [App\Http\Controllers\Admin\AuthorController::class, 'settlements']);

    // ---- 审核中心 ----
    Route::get('audit/books', [App\Http\Controllers\Admin\AuditController::class, 'pendingBooks']);
    Route::get('audit/chapters', [App\Http\Controllers\Admin\AuditController::class, 'pendingChapters']);
    Route::get('audit/chapters/{id}/preview', [App\Http\Controllers\Admin\AuditController::class, 'chapterPreview']);
    Route::get('audit/logs', [App\Http\Controllers\Admin\AuditController::class, 'logs']);

    // ---- 数据看板 / 报表 ----
    Route::get('dashboard/overview', [App\Http\Controllers\Admin\DashboardController::class, 'overview']);
    Route::get('dashboard/trend', [App\Http\Controllers\Admin\DashboardController::class, 'trend']);
    Route::get('dashboard/retention', [App\Http\Controllers\Admin\DashboardController::class, 'retention']);
    Route::get('dashboard/book-ranking', [App\Http\Controllers\Admin\DashboardController::class, 'bookRanking']);

    Route::get('analytics/revenue', [App\Http\Controllers\Admin\AnalyticsController::class, 'revenue']);
    Route::get('analytics/users', [App\Http\Controllers\Admin\AnalyticsController::class, 'users']);

    // ---- 订单（财务） ----
    Route::get('orders', [App\Http\Controllers\Admin\OrderController::class, 'index']);
    Route::get('orders/{id}', [App\Http\Controllers\Admin\OrderController::class, 'show']);
    Route::get('orders/reconcile', [App\Http\Controllers\Admin\OrderController::class, 'reconcile']);
    Route::post('orders/{id}/refund', [App\Http\Controllers\Admin\OrderController::class, 'refund'])->middleware('permission:order.refund');

    // ---- 权限管理 ----
    Route::get('departments', [App\Http\Controllers\Admin\RbacController::class, 'departments']);
    Route::post('departments', [App\Http\Controllers\Admin\RbacController::class, 'storeDepartment']);
    Route::put('departments/{id}', [App\Http\Controllers\Admin\RbacController::class, 'updateDepartment']);

    Route::get('roles', [App\Http\Controllers\Admin\RbacController::class, 'roles']);
    Route::post('roles', [App\Http\Controllers\Admin\RbacController::class, 'storeRole']);
    Route::put('roles/{id}', [App\Http\Controllers\Admin\RbacController::class, 'updateRole']);
    Route::get('roles/{id}/permissions', [App\Http\Controllers\Admin\RbacController::class, 'getRolePermissions']);
    Route::post('roles/{id}/permissions', [App\Http\Controllers\Admin\RbacController::class, 'setRolePermissions']);

    Route::get('permissions', [App\Http\Controllers\Admin\RbacController::class, 'permissions']);
    Route::post('permissions', [App\Http\Controllers\Admin\RbacController::class, 'storePermission']);

    Route::post('users/{id}/roles', [App\Http\Controllers\Admin\RbacController::class, 'setUserRoles']);
});
