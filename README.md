# Tradeflow API

## Prérequis
- [PHP](https://www.php.net/downloads.php)
- [Composer](https://getcomposer.org/download/)
- [Symfony CLI](https://symfony.com/download)

## Installation

```bash
# cloner le projet
git clone https://github.com/MelainGerard/tradeflow-api.git

# se déplacer dans le dossier
cd tradeflow-api

# installer les dépendances
composer install

# Créer un fichier .env.local et modifier les variables d'environnement
cp .env .env.local
# Ajouter la connexion à la base de données dans le fichier .env.local
echo 'DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"' >> .env.local

# Créer la base de données
php bin/console d:m:m

# Créer un utilisateur
php bin/console api:user:create

## Note : Il  faut indiquer le role "ROLE_ADMIN" pour pouvoir gérer l'application

# Lancer le serveur
symfony serve
```