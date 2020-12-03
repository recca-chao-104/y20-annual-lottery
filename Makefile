.PHONY: key.genrate
key.generate:
	php artisan key:generate

.PHONY: key.genrate.test
key.generate.test:
	php artisan key:generate  --env=testing

.PHONY: program.start
program.start:
	touch database/database.sqlite && php artisan program:start

.PHONY: test.start
test.start:
	php artisan key:generate  --env=testing
	touch database/database_test.sqlite && php artisan test