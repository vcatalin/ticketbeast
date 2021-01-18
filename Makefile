db-up:
	echo "Starting the database.." && \
	docker-compose up -d

db-down:
	docker-compose down

run-dev: db-up
	php artisan serve

stop-dev: db-down
