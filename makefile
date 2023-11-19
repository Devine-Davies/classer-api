# 
# Build
#

start:
	./vendor/bin/sail up -d	

refresh:
	./vendor/bin/sail php artisan optimize:clear
	make db.refresh

# 
# Datebase
#

db.refresh:
	./vendor/bin/sail php artisan migrate:refresh --seed

db.seed:
	./vendor/bin/sail php artisan db:seed

db.migrate:
	./vendor/bin/sail php artisan migrate

db.reset:
	./vendor/bin/sail php artisan migrate:reset

db.create:
	./vendor/bin/sail php artisan migrate:install

# 
# Make
#

make.ctrl:
	./vendor/bin/sail php artisan make:controller $(name)Controller --api

make.api-ctrl:
	./vendor/bin/sail php artisan make:controller Api/$(name)Controller --api

make.migration:
	./vendor/bin/sail php artisan make:migration $(name)

make.cmd:
	./vendor/bin/sail php artisan make:command $(name)

make.model:
	./vendor/bin/sail php artisan make:model $(name)

make.mail:
	./vendor/bin/sail php artisan make:mail $(name)

# 
# SECHEDULE
#

schedule:
	@echo "Schedule run"
	./vendor/bin/sail php artisan schedule:run

schedule.cache:
	@echo "Schedule cache"
	./vendor/bin/sail php artisan schedule:clear-cache

schedule.list:
	@echo "Schedule list"
	./vendor/bin/sail php artisan schedule:list

schedule.run:
	@echo "Schedule run"
	./vendor/bin/sail php artisan schedule:run

schedule.work:
	@echo "Schedule work"
	./vendor/bin/sail php artisan schedule:work

# 
# Testing
#

test:
	./vendor/bin/sail php artisan test

test-coverage:
	./vendor/bin/sail php artisan test --coverage-text

test-coverage-html:
	./vendor/bin/sail php artisan test --coverage-html coverage

test-coverage-clover:
	./vendor/bin/sail php artisan test --coverage-clover coverage.xml


