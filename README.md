# LibreRooms

**LibreRooms** est une web-app open source de gestion de réservation de salles, développée en Laravel. Elle permet aux organisations de mettre à disposition leurs espaces (salles de réunion, locaux associatifs) et de gérer les réservations de manière simple et efficace.


<img src="docs/images/librerooms-1.png" height="45%" width="45%" > <img src="docs/images/librerooms-2.png" height="45%" width="45%" >
<img src="docs/images/librerooms-3.png" height="45%" width="45%" > <img src="docs/images/librerooms-4.png" height="45%" width="45%" >

## Fonctionnalités
### Propriétaires
- Gestion de plusieurs propriétaires de salles, avec chacun leur configuration, leurs options de facturation, leur monnaie, etc.
- Chaque propriétaire peut avoir plusieurs utilisateurs: lecteurs (peuvent voir les salles privées), modérateurs (peuvent gérer les réservations), administrateurs (peuvent créer/modifier des salles, modifier le propriétaire)

### Salles
- Création facile de salles, avec plusieurs possibilités de personnalisation (présentation, localisation, images, charte)
- Page de liste des salles et page de présentation des salles
- Tarification paramétrable (réservation courte, journée entière), possibilité de définir un fonctionnement à prix libre
- Fonctionnalité "secret" pour transmettre des codes d'accès avec un lien valable pour une durée limitée
- Visibilité publique ou restreinte des salles
- Possibilité d'ajouter des champs personnalisés au formulaire de réservation pour chaque salle
- Possibilité d'ajouter des options payantes pour la salle, sélectionnables pour chaque événement lors d'une réservation (utilisation du beamer, accès à la cuisine...)
- Possibilité d'ajouter des réductions pour la salle (fixes ou en pourcentage) - par ex. "Entité à but non lucratif"
- Gestion de salles avec différents fuseaux horaires

### Réservations
- Calendrier interactif avec visualisation des disponibilités
- Réservations multi-jours et multi-créneaux
- Flux de validation (en attente / confirmée / annulée / terminée)
- Génération de documents pdf (confirmations, factures)
- Notifications par email (confirmation, rappel, annulation) pour les propriétaires et les locataires
- Réservations possibles avec ou sans compte
- Option "don supplémentaire"
- Option "réduction particulière" (que le propriétaire peut accorder)

### Facturation
- Gestion basique de facturation (suivi des échéances, possibilité d'envoyer des rappels)
- Génération de factures avec données de paiement aux formats: SEPA avec QR code, QR-facture suisse, International (IBAN/BIC)
- Annulation / recréation des factures possible indépendamment des réservations

### Intégrations
- **CalDAV** : Synchronisation bidirectionnelle avec calendriers externes (permet par exemple de bloquer la salle sans passer par le système de réservation)
- **WebDAV** : Stockage des pdf de confirmations et factures sur serveur distant
- **OIDC** : Authentification SSO via fournisseurs d'identité externes (Nextcloud OIDC implémenté) - fusion des comptes selon email

### Gestion des utilisateur·ice·s
- Création / modification de comptes / vérification email / réinitialisation des mots de passe
- Hiérarchie de privilèges : admins globaux, admins des propriétaires, modérateur·ice·s (par propriétaire), lecteur·ice·s (par propriétaire)
- Comptes sans privilèges (permet de préremplir ses données de contact et voir ses réservations)

### Contacts
- Création de "Contacts": personne physique ou personne morale avec coordonnées
- Les "Contacts" peuvent être utilisés comme coordonnées du locataire lors de la réservation (préremplissage pour locataires réguliers)
- Les propriétaires sont également associés à un "Contact"
- Les "Contacts" peuvent être partagés entre plusieurs utilisateur·ice·s

### Configuration
- Possibilité de définir des configurations système par défaut utilisables par les propriétaires (paramètres CalDAV, WebDAV, etc.)
- Gestion des fournisseurs d'identité
- Interface de configuration initiale de l'environnement (base de donnée)

### Vues mobile
- Vues mobile-friendly

## Prérequis
- PHP 8.2+
- Composer 2.x
- Node.js 20+ et npm
- SQLite/MariaDB/MySQL/PostgreSQL
- Apache ou Nginx
- (Optionnel) Serveur CalDAV pour la synchronisation calendrier
- (Optionnel) Serveur WebDAV pour la synchronisation des documents

## Installation
### 1. Installer l'application
```bash
git clone https://github.com/theosche/libre-rooms
cd libreRooms
./install.sh
```

### 2. Créer la base de données (exemple)
```sql
CREATE DATABASE librerooms;
CREATE USER 'librerooms'@'localhost' IDENTIFIED BY 'votre_mot_de_passe';
GRANT ALL PRIVILEGES ON librerooms.* TO 'librerooms'@'localhost';
FLUSH PRIVILEGES;
```

### 3. Configurer un serveur web
Diriger un serveur web vers /project/path/public
Exemple de config vhost apache [ici](docs/apache/apacheVhost.librerooms.conf.example).

### 4. Ajouter une entrée crontab pour les tâches planifiées
```bash
sudo crontab -u www-data -e
* * * * * cd /var/www/html/libreRooms && php artisan schedule:run >> /dev/null 2>&1
```

### 5. Configuration initiale
Accédez à l'application via votre navigateur. Un assistant de configuration vous guidera pour :

1. Connecter la base de données et définir les paramètres de l'environnement
2. Créer un compte d'admin global
3. Configurer les paramètres système obligatoires

## Utilisation
### Premiers pas
1. **Créer un contact**: Avant de créer un premier propriétaire, il faut créer le contact qui y sera associé
2. **Créer un propriétaire**
3. **Créer une salle** : Associez une salle au nouveau propriétaire. Configurez les différents paramètres. Ajouter des options, des réductions ou des champs personnalisés si souhaité
4. **C'est prêt !** Les utilisateurs peuvent visualiser la salle et la réserver.

## Licence
Ce projet est distribué sous licence GNU GENERAL PUBLIC LICENSE.

## Contribution
Les contributions sont les bienvenues ! N'hésitez pas à ouvrir une issue ou un pull request sur GitHub.
