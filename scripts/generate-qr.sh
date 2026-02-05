#!/bin/bash

# Script pour générer un QR code de test
# Nécessite: jq, qrencode

set -e

# Vérifier que jq est installé
if ! command -v jq &> /dev/null; then
    echo "❌ jq n'est pas installé. Installation: sudo apt install jq"
    exit 1
fi

echo "🔐 Authentification..."

# Se connecter et récupérer le token
TOKEN=$(curl -s -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}' \
  | jq -r '.token')

if [ "$TOKEN" == "null" ] || [ -z "$TOKEN" ]; then
    echo "❌ Échec de l'authentification"
    exit 1
fi

echo "✅ Authentifié"

# Demander l'ID du projet
read -p "ID du projet (défaut: 1): " PROJECT_ID
PROJECT_ID=${PROJECT_ID:-1}

echo "📷 Génération du QR code pour le projet $PROJECT_ID..."

# Générer le QR code
RESPONSE=$(curl -s -X POST "http://localhost:8000/api/admin/projects/$PROJECT_ID/qr-code/generate" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json")

# Extraire les données
QR_DATA=$(echo "$RESPONSE" | jq -r '.qr_code_data')
PROJECT_NAME=$(echo "$RESPONSE" | jq -r '.project_name')

if [ "$QR_DATA" == "null" ]; then
    echo "❌ Erreur lors de la génération du QR code"
    echo "$RESPONSE"
    exit 1
fi

echo "✅ QR code généré pour: $PROJECT_NAME"
echo ""
echo "Données du QR code:"
echo "$QR_DATA" | jq .
echo ""

# Générer l'image QR code si qrencode est installé
if command -v qrencode &> /dev/null; then
    mkdir -p qr-codes
    FILENAME="qr-codes/project-$PROJECT_ID-$(date +%Y%m%d-%H%M%S).png"
    echo "$QR_DATA" | qrencode -o "$FILENAME" -s 10
    echo "✅ Image QR code sauvegardée: $FILENAME"
    
    # Ouvrir l'image si possible
    if command -v xdg-open &> /dev/null; then
        xdg-open "$FILENAME"
    fi
else
    echo "💡 Installez qrencode pour générer une image: sudo apt install qrencode"
    echo ""
    echo "Vous pouvez utiliser ce JSON pour générer un QR code en ligne:"
    echo "$QR_DATA"
fi
