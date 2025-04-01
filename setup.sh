#!/bin/bash

# Obtém o IP do servidor
SERVER_IP=$(hostname -I | awk '{print $1}')
echo "IP do servidor detectado: $SERVER_IP"

echo "Criando o arquivo .env"
ENV_FILE=".env"
rm -f $ENV_FILE
cat <<EOL >> $ENV_FILE
#--------------------------------------------------------------------
# ENVIRONMENT
#--------------------------------------------------------------------

CI_ENVIRONMENT = production

#--------------------------------------------------------------------
# APP
#--------------------------------------------------------------------

app.baseURL = 'http://$SERVER_IP:8080'

#--------------------------------------------------------------------
# DATABASE
#--------------------------------------------------------------------

database.default.hostname = postgres
database.default.database = seplag_db
database.default.username = seplag
database.default.password = seplag@123
database.default.DBDriver = Postgre
database.default.DBPrefix =
database.default.charset = utf8
database.default.DBCollat = utf8_general_ci
database.default.port = 5432

#--------------------------------------------------------------------
# JWT AUTHENTICATION
#--------------------------------------------------------------------

jwt.privateKey = SEPLAG-8cd98f00B204e9800998ECf8427e
jwt.lifeTime = 300
jwt.ipSeverAuth = ::1

#--------------------------------------------------------------------
# MIN.IO
#--------------------------------------------------------------------

minio.endpoint = http://$SERVER_IP:9000
minio.access.key = admin
minio.secret.key = seplag@123
minio.region = us-east-1
minio.default.bucket = uploads
minio.use.path.style = true
EOL

echo "Iniciando o processo de setup dos containers..."
docker-compose down
docker-compose build --no-cache
docker-compose up -d

echo "Aguardando os containers..."
sleep 10

echo "Atribuir 777 na writable e no swagger.json..."
docker exec -it app bash -c "cd /var/www/html && \
chown -R www-data:www-data writable && \
chmod -R 777 writable && \
find writable -type d -exec chmod 777 {} + && \
find writable -type f -exec chmod 666 {} + && \
chmod 777 public/swagger.json"

echo "Instalando dependencias composer..."
docker exec -it app composer install --no-interaction --prefer-dist --optimize-autoloader

echo "Executando migrations..."
docker exec -it app php spark migrate

echo "Executando seeders..."
docker exec -it app php spark db:seed AllSeeder

echo "Setup concluido com sucesso!"
docker-compose ps