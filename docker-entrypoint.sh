#!/bin/bash
set -e

echo "Aguardando PostgreSQL..."
while ! nc -z postgres 5432; do   
  sleep 1
done
echo "PostgreSQL pronto!"

# Executa migrations e seeds
php spark migrate
php spark db:seed DatabaseSeeder

# Inicia o Apache
apache2-foreground
