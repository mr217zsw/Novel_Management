# API 接口文档

所有接口统一响应格式：

```json
{
  "code": 0,
  "message": "ok",
  "data": {},
  "time": 1700000000
}
```

认证方式：`Authorization: Bearer <jwt_token>`

---

## 一、用户端接口（`/api/v1`）

### 认证

| 方法 | 路径 | 说明 | 登录 |
|------|------|------|------|
| POST | `/api/v1/auth/login` | 小程序 openid 登录 | 否 |
| POST | `/api/v1/auth/login/phone` | 手机号验证码登录 | 否 |
| POST | `/api/v1/auth/sms-code` | 发送验证码 | 否 |
| POST | `/api/v1/auth/refresh` | 刷新 Token | 是 |
| POST | `/api/v1/auth/logout` | 登出 | 是 |
| GET | `/api/v1/auth/me` | 当前用户 | 是 |

### 书籍

| 方法 | 路径 | 说明 | 登录 |
|------|------|------|------|
| GET | `/api/v1/books` | 小说列表 | 否 |
| GET | `/api/v1/books/{id}` | 小说详情 | 否 |
| GET | `/api/v1/books/{id}/chapters` | 章节列表 | 否 |

### 章节（内容存 OSS）

| 方法 | 路径 | 说明 | 登录 |
|------|------|------|------|
| GET | `/api/v1/chapters/{id}` | 章节内容（付费校验） | 是 |
| POST | `/api/v1/chapters/{id}/progress` | 上报阅读进度 | 是 |

### 评论

| 方法 | 路径 | 说明 | 登录 |
|------|------|------|------|
| GET | `/api/v1/comments` | 评论列表 | 否 |
| POST | `/api/v1/comments` | 发表评论 | 是 |
| POST | `/api/v1/comments/{id}/like` | 点赞 | 是 |

### OSS 直传

| 方法 | 路径 | 说明 | 登录 |
|------|------|------|------|
| GET | `/api/v1/oss/sts-token` | 获取上传凭证 | 是 |
| POST | `/api/v1/oss/complete` | 上传完成登记 | 是 |
| POST | `/api/v1/oss/chapter` | 登记章节内容 | 是 |

### 支付 / 广告

| 方法 | 路径 | 说明 | 登录 |
|------|------|------|------|
| POST | `/api/v1/payment/orders` | 创建支付订单 | 是 |
| POST | `/api/v1/payment/callback/{platform}` | 支付回调 | 否 |
| POST | `/api/v1/payment/verify` | 苹果收据校验 | 否 |
| GET | `/api/v1/payment/orders/{order_no}` | 查询订单 | 是 |
| POST | `/api/v1/ad/reward` | 激励视频奖励 | 是 |

### 用户中心

| 方法 | 路径 | 说明 | 登录 |
|------|------|------|------|
| GET | `/api/v1/user/profile` | 用户资料 | 是 |
| PUT | `/api/v1/user/profile` | 更新资料 | 是 |
| GET | `/api/v1/user/shelf` | 我的书架 | 是 |
| GET | `/api/v1/user/products` | 充值/VIP 商品 | 是 |

---

## 二、管理后台接口（`/api/admin`）

### 认证
| 方法 | 路径 | 说明 |
|------|------|------|
| POST | `/api/admin/login` | 后台账号密码登录 |

### 投流运营

| 方法 | 路径 | 说明 |
|------|------|------|
| GET/POST | `/api/admin/channels` | 渠道列表/创建 |
| PUT/DELETE | `/api/admin/channels/{id}` | 更新/删除渠道 |
| GET/POST | `/api/admin/campaigns` | 投放计划列表/创建 |
| PUT/DELETE | `/api/admin/campaigns/{id}` | 更新/删除计划 |
| POST | `/api/admin/campaigns/{id}/toggle` | 暂停/恢复投放 |
| GET/POST | `/api/admin/materials` | 素材列表/登记 |
| POST | `/api/admin/materials/{id}/audit` | 素材审核 |
| DELETE | `/api/admin/materials/{id}` | 删除素材 |
| GET | `/api/admin/attributions` | 归因明细 |
| GET | `/api/admin/attributions/roi` | 渠道 ROI 分析 |
| GET | `/api/admin/attributions/book/{id}` | 单本书 ROI |

### 内容版权

| 方法 | 路径 | 说明 |
|------|------|------|
| GET/POST | `/api/admin/books` | 书籍列表/创建 |
| GET/PUT/DELETE | `/api/admin/books/{id}` | 详情/更新/删除 |
| POST | `/api/admin/books/{id}/audit` | 书籍审核 |
| POST | `/api/admin/books/{id}/toggle` | 上/下架 |
| GET/POST | `/api/admin/books/{bookId}/chapters` | 章节列表/创建 |
| POST | `/api/admin/chapters/{id}/audit` | 章节审核 |
| GET/POST | `/api/admin/copyrights` | 版权列表/登记 |
| POST | `/api/admin/copyrights/{id}/pay` | 版权付款审批 |
| GET/POST | `/api/admin/authors` | 作者列表/签约 |
| PUT | `/api/admin/authors/{id}` | 更新作者 |
| POST | `/api/admin/authors/{id}/contract` | 上传合同 |
| GET | `/api/admin/authors/{id}/settlements` | 作者分成 |

### 审核中心

| 方法 | 路径 | 说明 |
|------|------|------|
| GET | `/api/admin/audit/books` | 待审核书籍 |
| GET | `/api/admin/audit/chapters` | 待审核章节 |
| GET | `/api/admin/audit/chapters/{id}/preview` | 章节预览 |
| GET | `/api/admin/audit/logs` | 审核日志 |

### 数据看板

| 方法 | 路径 | 说明 |
|------|------|------|
| GET | `/api/admin/dashboard/overview` | 实时概览 |
| GET | `/api/admin/dashboard/trend` | 趋势数据 |
| GET | `/api/admin/dashboard/retention` | 留存分析 |
| GET | `/api/admin/dashboard/book-ranking` | 书籍排行 |
| GET | `/api/admin/analytics/revenue` | 收入报表 |
| GET | `/api/admin/analytics/users` | 用户统计 |

### 订单 / 权限

| 方法 | 路径 | 说明 |
|------|------|------|
| GET | `/api/admin/orders` | 订单列表 |
| GET | `/api/admin/orders/{id}` | 订单详情 |
| GET | `/api/admin/orders/reconcile` | 对账 |
| POST | `/api/admin/orders/{id}/refund` | 退款 |
| GET/POST | `/api/admin/departments` | 部门管理 |
| GET/POST | `/api/admin/roles` | 角色管理 |
| GET/POST | `/api/admin/permissions` | 权限管理 |
| POST | `/api/admin/roles/{id}/permissions` | 角色授权 |
| POST | `/api/admin/users/{id}/roles` | 用户分配角色 |
