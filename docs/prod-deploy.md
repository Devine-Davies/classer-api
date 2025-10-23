Apply changes to .env file:

APP_ENV=production
APP_DEBUG=false
Make sure that you are optimizing Composer's class autoloader map (docs):

composer dump-autoload --optimize
or along install: composer install --optimize-autoloader --no-dev
or during update: composer update --optimize-autoloader
Optimizing Configuration Loading:

php artisan config:cache
Discover and cache the application's events and listeners:

php artisan event:cache
Optimizing Route registration:

php artisan route:cache
Compile all of the application's Blade templates:

php artisan view:cache
Cache the framework bootstrap files:

php artisan optimize
(Optional) Asset Bundling:

Using Vite Laravel Plugin (docs): npm run build
Using Laravel Mix (docs): npm run production
(Optional) Generate the encryption keys Laravel Passport needs (docs):

php artisan passport:keys
(Optional) Start Laravel task scheduler by adding the following Cron entry (docs):

* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
(Optional) Install, config and start the Supervisor (docs):

(Optional) Create a symbolic link from public/storage to storage/app/public (docs):

php artisan storage:link
