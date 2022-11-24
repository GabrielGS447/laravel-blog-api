start:
	docker-compose up --build -d || \
	docker compose up --build -d

stop:
	docker-compose down || \
	docker compose down

start-in-container:
	php artisan migrate && php artisan serve --host=0.0.0.0 --port=8000

seed:
	php artisan migrate:fresh --seed

seed-container:
	docker-compose exec app php artisan migrate:fresh --seed || \
	docker compose exec app php artisan migrate:fresh --seed