FROM shinsenter/symfony:latest

WORKDIR /var/www/html

# Crée les dossiers nécessaires avant de copier (pour éviter de causer des soucis avec les droits)
RUN mkdir -p var/cache var/log public/assets

# Copie du projet
COPY . /var/www/html/

# Installe les dépendances Composer
RUN composer install --no-interaction --optimize-autoloader --no-scripts

# Donne les droits sur le script "entrypoint.sh"
RUN chmod +x /var/www/html/entrypoint.sh

# Définit le point d'entrée avec le bon chemin
ENTRYPOINT ["/var/www/html/entrypoint.sh"]
CMD ["php-fpm"]

# Compile les assets
RUN php bin/console assets:install --symlink --relative
