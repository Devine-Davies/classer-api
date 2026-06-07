#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html

# Clear stale scheduler mutexes before starting the long-running loop.
php artisan schedule:clear-cache || true

echo "[schedule-runner] Starting scheduler loop (interval: ${SCHEDULE_RUN_INTERVAL:-2}s)"

while true; do
  php artisan schedule:run --no-ansi || true
  sleep "${SCHEDULE_RUN_INTERVAL:-2}"
done
