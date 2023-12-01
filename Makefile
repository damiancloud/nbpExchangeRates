CONTAINER_NAME = app-nbp

start:
	docker-compose build
	composer install
	
stop:
	docker stop $(CONTAINER_NAME) 
	docker stop $(CONTAINER_NAME)-nginex

up:
	docker-compose up --force-recreate

console: 
	docker exec -it $(CONTAINER_NAME) bash

down:
	docker-compose down

phpstan:
	composer --working-dir=tools/phpstan install
	tools/phpstan/vendor/bin/phpstan analyse src
