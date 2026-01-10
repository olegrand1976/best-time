# MailHog - Configuration et Utilisation

## 🚀 Démarrage

MailHog est automatiquement lancé avec Docker Compose :

```bash
docker compose up -d mailhog
```

## 🌐 Accès

- **Interface Web MailHog** : http://localhost:9025
- **Port SMTP** : `1125` (externe) / `1025` (interne dans Docker)

## 📧 Configuration Laravel

La configuration mail est déjà définie dans `docker-compose.yml` pour utiliser MailHog :

```env
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=noreply@besttime.test
MAIL_FROM_NAME="Best Time"
```

## 🧪 Test d'envoi d'email

### Via la commande Artisan

```bash
docker compose exec php-fpm php artisan mail:test admin@besttime.test
```

### Via Tinker

```bash
docker compose exec php-fpm php artisan tinker
```

Puis dans Tinker :
```php
Mail::raw('Test email', function($m) {
    $m->to('test@example.com')->subject('Test');
});
```

### Via le code Laravel

```php
use Illuminate\Support\Facades\Mail;

Mail::raw('Message de test', function ($message) {
    $message->to('user@example.com')
            ->subject('Sujet du message');
});
```

## 📋 Visualisation des emails

1. Ouvrez votre navigateur : http://localhost:9025
2. Tous les emails envoyés par l'application s'affichent dans l'interface
3. Vous pouvez voir :
   - L'expéditeur et le destinataire
   - Le sujet
   - Le contenu HTML et texte
   - Les pièces jointes (si présentes)
   - Les en-têtes complets

## 🔄 Réinitialiser MailHog

Pour vider tous les emails :

1. Via l'interface web : Cliquez sur "Delete All"
2. Ou redémarrez le conteneur :
   ```bash
   docker compose restart mailhog
   ```

## ✅ Avantages

- **Développement local** : Aucun email réel n'est envoyé
- **Test rapide** : Visualisation immédiate des emails
- **Pas de configuration SMTP complexe** : Tout fonctionne en local
- **API disponible** : http://localhost:9025/api/v2/messages pour l'automatisation

## 📝 Notes

- MailHog est uniquement pour le développement
- Les emails sont stockés en mémoire (perdus au redémarrage)
- Le port externe SMTP est `1125` pour éviter les conflits (port interne `1025`)
