


# Development

## Docker
docker exec -it classer-api-laravel.test-1 bash
php artisan db:wipe
php artisan migrate:refresh --seed

## PHP MyAdmin
- http://localhost:8080/
- DB_USERNAME=sail
- DB_PASSWORD=password

## XAMP (Windows Optional)
- https://code.tutsplus.com/how-to-install-laravel--cms-93381t
- php artisan serve


## Quick Commands

cd public_html/api/ && git pull && cd ../../

php public_html/api/artisan schedule:run
php public_html/api/artisan schedule:list
php public_html/api/artisan schedule:clear-cache
* * * * * php /home/u329348820/domains/classermedia.com/public_html/api/artisan schedule:work >> /dev/null 2>&1

cat public_html/api/storage/logs/schedule/auto-login-reminder.log 

rm public_html/api/storage/logs/schedule/auto-login-reminder.log 

crontab -l -u username => list the jobs set for that username
crontab -e -u username => edit the jobs for that username
crontab -r - u username => remove the jobs for that username

* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1
php /home/u329348820/domains/classermedia.com/public_html/api/artisan schedule:list

# Scripts

Create AWS Structure
```bash
$ chmod +x ./scripts/*.sh 2>/dev/null || true
$ ./scripts/create_aws_structure.sh
$ ./scripts/clean_aws_structure.sh
```

# CRON Jobs

| Cron Job                 | Schedule   | type        | Description |
|--------------------------|------------|-------------|-------------|
| HPanel                   | * * * * *  | master      | Send trial code to users |
| app:auto-login-reminder  | hourly     | frameWork   | Send auto login reminder to users |
| app:auto-login-reminder  | hourly     | frameWork   | Send auto login reminder to users |

