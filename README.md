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

### 🔧 Project Initialization

Once inside the container, run the following:

```bash
# Reset and seed the database
php artisan db:wipe
php artisan migrate:refresh

# Run application-specific seeders
php artisan db:seed --class=SetupAppSeeder
php artisan db:seed --class=SetupTestAccountsSeeder
# php artisan db:seed --class=LiveBackupSeeder

# We can also assign subscriptions
php artisan subscription:activate skywalker@classermedia.com T017A42C
``` 

## 🛠️ Jobs's
In order to ensure system robustness, it is essential to implement a queue-based architecture that allows for reliable message retry and consumption. These jobs can be seen and updated in `App\Console\Kernel` and they are designed to be executed under one main long running process.

| Namespace      | Queue Type | Cadence             | Description                                                     |
|----------------|------------|---------------------|-----------------------------------------------------------------|
| **System**     | `mail`     | On-demand / Immediate | Sends automated emails (e.g. AccountVerify, PasswordReset)    |
| **CloudShare** | `verify`   | Every second         | Verifies uploads to the S3 bucket                              |
|                | `expire`   | Every 20 seconds     | Expires uploads to the S3 bucket                               |

#### Simulate CRON (Testing)
We can simulate a long-running process to support development and testing by executing the following command:

```bash
php artisan schedule:clear
while true; do php artisan schedule:run; sleep 2; done
```

##### Production
Use the following to manage CRON jobs:

```bash
* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1
```

## 🧪 Tools

### 🐘 PHPMyAdmin

Access the database with PHPMyAdmin:

* 🌐 URL: [http://localhost:8080/](http://localhost:8080/)
* 👤 **Username:** `sail`
* 🔒 **Password:** `password`

### Website
* 🌐 Home http://localhost
* 🌐 Admin http://localhost/auth/admin/login
  * 👤 **Username:** `rdd@example.com` Be sure to set this in .env APP_ADMIN_EMAILS
  * 🔒 **Password:** `password1`

### MailPit
* Home http://127.0.0.1:8025

### 🪟 Optional: XAMPP (for Windows) @deprecated

If you're using Windows and prefer XAMPP:

* [How to install Laravel with XAMPP](https://code.tutsplus.com/how-to-install-laravel--cms-93381t)
* Run your server with:

  ```bash
  php artisan serve
  ```

## ⚡ AWS API Gateway

This project’s routes have been converted into an OpenAPI 3.0.3 specification (openapi.yaml). The specification describes all available API endpoints, their HTTP methods, security requirements, and example request/response bodies.

```bash
$ php artisan route:list --path=api --json > routes.json
$ # as AI to gen OpenAPI 3 Yaml spec and import that into AWS API Gateway
```

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