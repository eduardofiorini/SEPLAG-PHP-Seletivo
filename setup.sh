#!/bin/bash

echo "Iniciando o processo de setup dos containers..."
docker-compose down
docker-compose build --no-cache
docker-compose up -d

echo "Aguardando os containers..."
sleep 10

echo "Instalando dependencias composer..."
docker exec -it app composer install --no-interaction --prefer-dist --optimize-autoloader

echo "Executando migrations..."
docker exec -it app php spark migrate

echo "Executando seeders..."
docker exec -it app php spark db:seed AllSeeder

echo "Setup concluido com sucesso!"
docker-compose ps