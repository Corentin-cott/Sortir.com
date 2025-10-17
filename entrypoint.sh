#!/bin/bash
set -e

# Compile les assets
php bin/console asset-map:compile

# Donnes les droits aux assets
chown -R www-data:www-data /var/www/html/var /var/www/html/public
chmod -R 775 /var/www/html/var /var/www/html/public

# Lance le scheduler
php bin/console messenger:consume scheduler_hello -vv --env=prod &

# Lance la commande de lancement du contenaire
exec "$@"