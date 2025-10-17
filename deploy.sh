git pull
docker compose down
rm -rf var/cache/*
docker compose build
docker compose up -d
