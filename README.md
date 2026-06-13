# 🚀 Classer API – Development Guide

Welcome to the Classer API! This guide will help you set up your development environment quickly and understand the key workflows for Docker, Laravel, CRON jobs, and more.

---

## 📦 Development Environment

### 🐳 Docker Setup

Spin up the Docker containers and access the Laravel app container:

```bash
# Start the Docker environment
docker compose up -d

# Access the Laravel container
docker exec -it classer-api-laravel.test-1 bash
```

### ☁️ LocalStack (AWS Emulator)

LocalStack is included in Docker Compose for local AWS integration testing (S3/SQS/SNS).

```bash
# Start LocalStack only (or include it in your normal up command)
docker compose up -d localstack
```

Use these environment values for local AWS access:

```bash
AWS_ACCESS_KEY_ID=test
AWS_SECRET_ACCESS_KEY=test
AWS_DEFAULT_REGION=us-east-1
AWS_ENDPOINT=http://localstack:4566
AWS_USE_PATH_STYLE_ENDPOINT=true
```

If you use the `s3` disk, set `AWS_BUCKET_NAME` to your local bucket name.

### 🔧 Project Initialization

Once inside the container, run the following:

```bash
# Reset and seed the database
php artisan db:wipe
php artisan migrate:refresh

# Run application-specific seeders
php artisan db:seed --class=SetupAppSeeder
php artisan db:seed --class=SetupTestAccountsSeeder
php artisan db:seed --class=SetupOrdersSeeder
# php artisan db:seed --class=LiveBackupSeeder

# We can also assign subscriptions
php artisan subscription:activate skywalker@classermedia.com T017A42C
```

## 🛠️ Jobs & Queues

This app uses two runtime layers:

- Always-on queue workers (Docker services) for continuous queue consumption.
- Laravel Scheduler for scheduled commands and cron-style orchestration.

Scheduler definitions are loaded from `config/classer.php` and executed by `App\Console\Kernel`.

When you run `docker compose up -d`, these job-related containers start automatically:

- `jobs.runner` (runs scheduler loop)
- `jobs.worker.mail` (consumes `database` connection queue `mail,default`)
- `jobs.worker.cloudshare.verify` (consumes `cloudshare` queue `verify`)
- `jobs.worker.cloudshare.expire` (consumes `cloudshare` queue `expire`)

`jobs.runner` runs:

```bash
scripts/jobs/schedule-runner.sh
```

This loop executes `php artisan schedule:run` every few seconds.

### Current Scheduler Jobs

Queue-worker scheduler entries are controlled by `SCHEDULE_QUEUE_WORKERS`:

- `true`: scheduler includes queue worker commands from `config/classer.php`.
- `false`: scheduler does not spawn queue workers (recommended with dedicated Docker worker services).

In Docker, compose sets `SCHEDULE_QUEUE_WORKERS=false` to prevent duplicate consumers.

| Job Key            | Command                                                                                    | Queue Connection                        | Queue Name | Cron Expression (default)       |
| ------------------ | ------------------------------------------------------------------------------------------ | --------------------------------------- | ---------- | ------------------------------- |
| `mail`             | `queue:work --queue=mail --stop-when-empty --sleep=1 --tries=3 --timeout=120`              | default connection (`QUEUE_CONNECTION`) | `mail`     | `* * * * *` (every minute)      |
| `cloudShareVerify` | `queue:work cloudshare --queue=verify --stop-when-empty --sleep=1 --tries=3 --timeout=300` | `cloudshare`                            | `verify`   | `0 */4 * * *` (every 4 hours)   |
| `cloudShareExpire` | `queue:work cloudshare --queue=expire --stop-when-empty --sleep=1 --tries=3 --timeout=600` | `cloudshare`                            | `expire`   | `0 0 * * *` (daily at midnight) |

### Queue Connections

Defined in `config/queue.php`:

| Connection   | Driver     | Backing Table      | Notes                                      |
| ------------ | ---------- | ------------------ | ------------------------------------------ |
| `database`   | `database` | `jobs`             | General queue backend                      |
| `cloudshare` | `database` | `cloud_share_jobs` | Dedicated CloudShare queue backend         |
| `sync`       | `sync`     | N/A                | Immediate execution, no worker consumption |

Important: the `mail` scheduler worker uses the default queue connection. For queued mail processing via workers, ensure `QUEUE_CONNECTION` is set to `database` (not `sync`).

### Practical Runtime Flow (Docker)

1. `docker compose up -d` starts `jobs.runner`.
2. `docker compose up -d` also starts dedicated queue worker services.
3. `jobs.runner` runs `scripts/jobs/schedule-runner.sh`.
4. The script loops `php artisan schedule:run` for scheduled tasks.
5. Queue jobs are consumed continuously by `jobs.worker.*` services.

Current queue usage in app jobs:

- Mail jobs (`App\\Jobs\\Mail*`) publish to `mail`.
- `CloudShareVerifyUpload` publishes to `verify` on `cloudshare` connection.
- `CloudShareExpireUpload` publishes to `expire` on `cloudshare` connection.

#### Simulate CRON (Testing)

We can simulate a long-running process to support development and testing by executing the following command:

```bash
php artisan schedule:clear
while true; do php artisan schedule:run; sleep 2; done
```

Equivalent script (used by Docker):

```bash
./scripts/jobs/schedule-runner.sh
```

##### Production

Use the following system CRON entry to trigger Laravel Scheduler every minute:

```bash
* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1
```

Useful checks:

```bash
php artisan schedule:list
php artisan queue:failed
php artisan queue:monitor database:mail cloudshare:verify cloudshare:expire

# Docker logs for workers/scheduler
docker compose logs -f jobs.runner
docker compose logs -f jobs.worker.mail
docker compose logs -f jobs.worker.cloudshare.verify
docker compose logs -f jobs.worker.cloudshare.expire
```

## 🔗 Usfull Links

- Home: http://localhost
- Admin Login: http://localhost/auth/admin/login
- Insiders Classer Share: http://localhost/insiders/classer-share
- Promotions Redeem: http://localhost/promotions/redeem
- Mailpit (Email Inbox): http://localhost:8025
- PHPMyAdmin: http://localhost:8080

## 🧪 Tools

### 🐘 PHPMyAdmin

Access the database with PHPMyAdmin:

- 🌐 URL: [http://localhost:8080/](http://localhost:8080/)
- 👤 **Username:** `sail`
- 🔒 **Password:** `password`

### Website

- 🌐 Home http://localhost
- 🌐 Admin http://localhost/auth/admin/login
    - 👤 **Username:** `rdd@example.com` Be sure to set this in .env APP_ADMIN_EMAILS
    - 🔒 **Password:** `password1`

### MailPit

- Home http://127.0.0.1:8025

### 🪟 Optional: XAMPP (for Windows) @deprecated

If you're using Windows and prefer XAMPP:

- [How to install Laravel with XAMPP](https://code.tutsplus.com/how-to-install-laravel--cms-93381t)
- Run your server with:

    ```bash
    php artisan serve
    ```

## 📮 Postman Export

You can export API routes and convert them into Postman-compatible files.

Recommended flow (container-safe):

1. Export API routes JSON from Laravel:

```bash
./vendor/bin/sail php artisan route:list --path=api --json > api-routes.json
```

2. Generate Postman collection:

```bash
npm run routes:postman
```

3. Generate Postman environment:

```bash
npm run routes:postman:env
```

4. Or generate both in one command:

```bash
npm run routes:postman:all
```

Generated files:

- `api-routes.json`
- `postman-api-collection.json`
- `postman-api-environment.json`

Environment variables included in Postman environment:

- `baseUrl` (default: `http://localhost`)
- `sanctumToken` (set this in Postman before authenticated requests)

## ⚡ Quick Commands

Here are frequently used commands to manage the app and server:

```bash
# Update the API codebase
cd public_html/api/ && git pull && cd ../../

# Run Laravel schedule and inspect schedule status
php public_html/api/artisan schedule:run
php public_html/api/artisan schedule:list
php public_html/api/artisan schedule:clear-cache

# View & delete schedule logs
cat public_html/api/storage/logs/schedule/auto-login-reminder.log
rm public_html/api/storage/logs/schedule/auto-login-reminder.log
```

## 🛠️ Scripts

We use helper scripts for managing AWS structure.

```bash
# Make all scripts executable
chmod +x ./scripts/*.sh 2>/dev/null || true

# Create or clean AWS structure
./scripts/create_aws_structure.sh
./scripts/clean_aws_structure.sh
```
