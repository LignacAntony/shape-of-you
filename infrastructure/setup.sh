#!/bin/bash

# Le répertoire du script
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

# Fonction pour afficher l'aide
show_help() {
    echo "Usage: ./setup.sh [option]"
    echo "Options:"
    echo "  --setup     Configurer l'environnement complet (Nginx, PHP, PostgreSQL, Symfony)"
    echo "  --cleanup   Nettoyer l'environnement (arrêter les services et supprimer les configs)"
    echo "  --help      Afficher ce message d'aide"
}

# Si aucun argument n'est fourni, afficher l'aide
if [ $# -eq 0 ]; then
    show_help
    exit 1
fi

# Traiter les arguments
case "$1" in
    --setup)
        echo "Configuration de l'environnement Symfony..."
        cd "$DIR/ansible" && ansible-playbook playbooks/symfony_stack.yml -i inventory.ini
        ;;
    --cleanup)
        echo "Nettoyage de l'environnement..."
        cd "$DIR/ansible" && ansible-playbook -i inventory.ini cleanup.yml
        ;;
    --help)
        show_help
        ;;
    *)
        echo "Option non reconnue: $1"
        show_help
        exit 1
        ;;
esac

exit 0 