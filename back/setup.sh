#!/bin/bash

# Variables
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=blog_dict
DB_USERNAME=root
DB_PASSWORD=password

# Couleurs pour les messages
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Fonction pour vérifier si un conteneur Docker est en cours d'exécution
check_docker_container() {
    if [ "$(docker ps -q -f name=blog_dict_db)" ]; then
        echo -e "${GREEN}✓ Le conteneur MySQL est en cours d'exécution${NC}"
        return 0
    else
        echo -e "${RED}✗ Le conteneur MySQL n'est pas en cours d'exécution${NC}"
        return 1
    fi
}

# Fonction pour vérifier la connexion à la base de données
check_db_connection() {
    echo "Vérification de la connexion à la base de données..."
    if mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" -e "USE $DB_DATABASE;" 2>/dev/null; then
        echo -e "${GREEN}✓ Connexion à la base de données établie${NC}"
        return 0
    else
        echo -e "${RED}✗ Impossible de se connecter à la base de données${NC}"
        return 1
    fi
}

# Fonction pour copier le fichier .env
copy_env_file() {
    if [ ! -f ".env" ]; then
        echo "Création du fichier .env..."
        cp .env.example .env
        echo -e "${GREEN}✓ Fichier .env créé${NC}"
    else
        echo -e "${YELLOW}! Le fichier .env existe déjà${NC}"
    fi
}

# Fonction pour générer la clé d'application
generate_app_key() {
    echo "Génération de la clé d'application..."
    php artisan key:generate
    echo -e "${GREEN}✓ Clé d'application générée${NC}"
}

# Fonction pour mettre à jour le fichier .env
update_env_file() {
    echo "Mise à jour des informations de connexion à la base de données dans .env..."
    
    sed -i "s/DB_HOST=.*/DB_HOST=$DB_HOST/" .env
    sed -i "s/DB_PORT=.*/DB_PORT=$DB_PORT/" .env
    sed -i "s/DB_DATABASE=.*/DB_DATABASE=$DB_DATABASE/" .env
    sed -i "s/DB_USERNAME=.*/DB_USERNAME=$DB_USERNAME/" .env
    sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=$DB_PASSWORD/" .env
    
    echo -e "${GREEN}✓ Fichier .env mis à jour${NC}"
}

# Fonction pour créer les dossiers de logs
create_log_directories() {
    echo "Création des dossiers de logs..."
    mkdir -p storage/logs
    touch storage/logs/dict.log
    chmod -R 775 storage
    echo -e "${GREEN}✓ Dossiers de logs créés${NC}"
}

# Fonction pour exécuter les migrations
run_migrations() {
    echo "Exécution des migrations..."
    php artisan migrate
    echo -e "${GREEN}✓ Migrations exécutées${NC}"
}

# Fonction pour vider le cache
clear_cache() {
    echo "Nettoyage du cache..."
    php artisan cache:clear
    php artisan config:clear
    php artisan route:clear
    echo -e "${GREEN}✓ Cache nettoyé${NC}"
}

# Principale
echo "=== Configuration de l'application DICT ==="

# Vérifier Docker
check_docker_container
if [ $? -ne 0 ]; then
    echo "Démarrage du conteneur Docker..."
    docker-compose up -d
    sleep 5 # Attendre que MySQL démarre
    check_docker_container
    if [ $? -ne 0 ]; then
        echo -e "${RED}Erreur: Impossible de démarrer le conteneur Docker${NC}"
        exit 1
    fi
fi

# Copier et configurer .env
copy_env_file
update_env_file
generate_app_key

# Créer les dossiers de logs
create_log_directories

# Vérifier la connexion à la base de données
check_db_connection
if [ $? -ne 0 ]; then
    echo -e "${YELLOW}Attente de la disponibilité de la base de données...${NC}"
    sleep 10
    check_db_connection
    if [ $? -ne 0 ]; then
        echo -e "${RED}Erreur: Impossible de se connecter à la base de données après plusieurs tentatives${NC}"
        exit 1
    fi
fi

# Exécuter les migrations
run_migrations

# Vider le cache
clear_cache

echo -e "${GREEN}=== Configuration terminée avec succès ===${NC}"
echo -e "Pour démarrer le serveur: ${YELLOW}php artisan serve${NC}"