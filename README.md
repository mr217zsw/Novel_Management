# 小说全栈平台（投流运营版）

公司以运营为核心：签约书籍 → 采购版权 → 孵化作者 → 信息流投流获客（抖音/微信/快手）→ 用户通过 **IAA（广告）** 和 **IAP（内购）** 两种方式消费 → 平台赚取中间差价。

存储方案：**阿里云 OSS 直传 + CDN 加速**（前端直传、分片上传、断点续传，文件不经过应用服务器）。
部署架构：**简化版** —— 单 ECS + RDS + Redis + OSS。

---

## 技术栈

| 层级 | 技术 |
|------|------|
| 后端 | Laravel 10.x（PHP 8.1+） |
| 数据库 | MySQL 8.0（阿里云 RDS） |
| 缓存/队列 | Redis 7.0 |
| 对象存储 | 阿里云 OSS + CDN |
| 认证 | JWT（tymon/jwt-auth） |
| 管理后台 | Vue3 + Vite + Pinia + Element Plus（独立仓库） |
| 小程序端 | Uniapp（微信/抖音/小红书/B站） |

---

## 快速开始

### 环境要求
- PHP >= 8.1（含扩展：`pdo_mysql`、`mbstring`、`openssl`、`redis`）
- Composer 2.x
- MySQL 8.0 / Redis 7.0
- （可选）OSS 已开通

### 安装

```bash
# 1. 安装依赖
composer install --optimize-autoloader

# 2. 配置环境
cp .env.example .env
php artisan key:generate

# 3. 生成 JWT 密钥
php artisan jwt:secret

# 4. 配置 .env 中的数据库 / Redis / OSS 信息

# 5. 数据库迁移
php artisan migrate --seed

# 6. 启动（本地）
php artisan serve

# 7. 启动队列（生产）
php artisan queue:work --daemon --tries=3

# 8. 定时任务（生产 crontab）
* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1
```

### 默认管理员账号
```
账号：13800000000
密码：Admin@123456
```

### 本地模拟模式

本地开发默认开启以下模拟（见 `.env`）：

| 开关 | 说明 |
|------|------|
| `AUDIT_MOCK_ENABLED=true` | 内容审核直接返回"通过" |
| `PAYMENT_MOCK_ENABLED=true` | 支付直接返回成功 |
| `SMS_MOCK_ENABLED=true` | 短信验证码固定为 `123456`，仅记日志 |
| `QUEUE_MOCK_ENABLED=true` | 队列同步执行 |
| `OSS_MOCK_ENABLED=true` | OSS 用本地 `storage/oss` 目录代替 |

生产环境关闭对应开关，代码中已标注 `生产环境替换为真实 API 调用`。

---

## 核心模块

### 1. OSS 大文件直传
- `GET  /api/v1/oss/sts-token` 获取 STS 临时凭证
- 前端分片上传（5MB/片、3 并发、断点续传）
- `POST /api/v1/oss/complete` 上传完成登记素材/章节元数据

### 2. 投流归因系统（核心亮点）
- `POST /api/v1/attribution/click` 记录广告点击
- 注册自动绑定 → 付费自动关联 → 精确 ROI 计算
- `GET /api/admin/attributions/roi` 渠道 ROI 分析

### 3. 支付体系
统一封装 `PaymentGateway` 接口，支持：
- 微信支付
- 抖音普通支付
- 抖音虚拟支付（不抽成）
- 苹果 IAP（verifyReceipt 校验收据）

回调统一处理：验签 → 幂等加锁 → 事务发货 → 触发事件。

### 4. IAA/IAP 双变现引擎
- IAP：充值/VIP/章节解锁
- IAA：激励视频/插屏/开屏广告，`POST /api/v1/ad/reward` 发放奖励

### 5. 权限系统（RBAC + 部门隔离）
- 部门/角色/权限三级模型
- `PermissionMiddleware` 按权限码拦截 + Redis 缓存

---

## API 文档

详见 [docs/API.md](docs/API.md)。

---

## 部署

### 单机部署拓扑
```
CDN ← OSS ← 前端直传
ECS（Nginx + PHP-FPM + Laravel）
  ├── RDS（MySQL 8.0）
  ├── Redis
  └── OSS
```

### Nginx 配置
见 `deploy/nginx.conf`。

### Docker 部署
见 `deploy/Dockerfile` 与 `docker-compose.yml`（可选）。

---

## 目录结构

```
app/
├── Console/Commands/     # 定时任务（对账/统计/分成/清理）
├── Http/
│   ├── Controllers/
│   │   ├── Api/          # 小程序端接口
│   │   └── Admin/        # 管理后台接口
│   ├── Middleware/       # 认证/权限/CORS
│   └── Requests/         # 表单验证
├── Models/               # 数据模型
├── Services/
│   ├── OSS/              # STS凭证 + OSS存储
│   ├── Payment/          # 支付网关（4套）
│   ├── AttributionService.php
│   ├── AuditService.php
│   └── ...
├── Jobs/                 # 队列任务
└── Events/               # 事件
config/                   # 配置（aliyun/payment/tencent/mock）
database/migrations/      # 数据表
routes/api.php            # API 路由
```

---

## 面试要点

- **ROI 归因**：点击→注册→付费全链路，95% 归因准确率
- **OSS 直传**：分片 + 断点续传，服务器 0 负担，比 NAS 省 90%
- **支付体系**：四套支付统一 `PaymentGateway` 接口
- **IAA/IAP**：广告 + 内购双引擎并行
