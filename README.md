

Windows Setup - Using XAMP
https://code.tutsplus.com/how-to-install-laravel--cms-93381t

# CRON Jobs

| Cron Job                 | Schedule   | type        | Description |
|--------------------------|------------|-------------|-------------|
| HPanel                   | * * * * *  | master      | Send trial code to users |
| app:auto-login-reminder  | hourly     | frameWork   | Send auto login reminder to users |
| app:auto-login-reminder  | hourly     | frameWork   | Send auto login reminder to users |


# Quick Dev Commands1

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