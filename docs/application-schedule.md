# Application Schedule Outline

This document describes the current application scheduling setup and how to manage it.

## Overview

The scheduler is defined in [app/Console/Kernel.php](app/Console/Kernel.php) and reads job definitions from [config/classer.php](config/classer.php).

At runtime, each scheduled job:

- Runs via Laravel scheduler (`php artisan schedule:run`)
- Uses a cron expression from environment variables
- Uses `withoutOverlapping(...)` to prevent duplicate runs
- Runs in the background

## Current Scheduled Jobs

At a glance:

- Mail queue: runs every minute.
- Cloud-share verify queue: runs every 4 hours.
- Cloud-share expire queue: runs daily at midnight.

Technical reference:

- `mail`: env `CRON_EXPRESSION_MAIL`, default `* * * * *`, overlap lock 5 minutes.
- `cloudShareVerify`: env `CRON_EXPRESSION_CLOUD_SHARE_VERIFY`, default `0 */4 * * *`, overlap lock 30 minutes.
- `cloudShareExpire`: env `CRON_EXPRESSION_CLOUD_SHARE_EXPIRE`, default `0 0 * * *`, overlap lock 60 minutes.

## Required System Cron

Production servers must run the Laravel scheduler every minute:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

This matches the deployment guidance in [docs/prod-deploy.md](docs/prod-deploy.md).

## Environment Variables

Set or update these values in [.env](.env):

- `CRON_EXPRESSION_MAIL`
- `CRON_EXPRESSION_CLOUD_SHARE_VERIFY`
- `CRON_EXPRESSION_CLOUD_SHARE_EXPIRE`

Example:

```dotenv
CRON_EXPRESSION_MAIL="* * * * *"
CRON_EXPRESSION_CLOUD_SHARE_VERIFY="0 */4 * * *"
CRON_EXPRESSION_CLOUD_SHARE_EXPIRE="0 0 * * *"
```

After changes:

```bash
php artisan config:clear
php artisan config:cache
```

## How To Verify Schedules

```bash
php artisan schedule:list
```

Run due tasks now:

```bash
php artisan schedule:run
```

Watch queue workers/log output as needed to confirm jobs execute correctly.

## Change Process

1. Update cron expressions in [.env](.env).
2. If needed, update job command or overlap lock in [config/classer.php](config/classer.php).
3. Clear/cache config.
4. Validate with `php artisan schedule:list`.
5. Deploy and monitor first run.
