# SkillPathOff
Skill Path est une plateforme intelligente qui permet de faciliter le parcours d’apprentissage. Elle est développée dans le cadre de notre parcours académique.
Une application Symfony complète avec fonctionnalités avancées incluant gestion de calendrier, chatbot IA, reconnaissance vocale, et intégration de machine learning.

## Caractéristiques principales

- **Gestion de calendrier** - Calendrier interactif avec FullCalendar
- **Chatbot IA** - Chatbot intelligent intégré
- **Reconnaissance vocale** - Contrôleur de reconnaissance vocale
- **Génération de QR Code** - Création dynamique de codes QR
- **Génération PDF** - Export des documents en PDF
- **Machine Learning** - Pipeline d'entraînement et prédiction de modèles
- **Authentification OAuth2** - Sécurité avancée avec KnpUniversity
- **Upload de fichiers** - Gestion des uploads avec Vich Uploader
- **Pagination** - Système de pagination KnpPaginator
- **Internationalisation** - Support multilingue

##  Prérequis

- PHP 8.2+
- Docker & Docker Compose
- Composer
- Python 3.8+ (pour ML)

##  Installation

### 1. Cloner et installer les dépendances

```bash
cd SkillPathOff
composer install
npm install
```

### 2. Configuration de l'environnement

```bash
# Copier le fichier .env.example
cp .env .env.local

# Générer la clé d'application
php bin/console key:generate
```

### 3. Base de données

```bash
# Créer la base de données
php bin/console doctrine:database:create

# Exécuter les migrations
php bin/console doctrine:migrations:migrate
```

### 4. Assets

```bash
# Compiler les assets
npm run build

# En développement (watch mode)
npm run watch
```

##  Docker

Pour démarrer l'application avec Docker :

```bash
docker-compose up -d
```

Cela démarre :
- L'application Symfony (port 8000)
- MariaDB (base de données)
- Mail server (MailHog)

## Structure du projet

```
src/
├── Command/           # Commandes console personnalisées
├── Controller/        # Contrôleurs
├── Entity/           # Entités Doctrine
├── EventListener/    # Event listeners
├── EventSubscriber/  # Event subscribers
├── Form/             # Formulaires
├── Repository/       # Repositories Doctrine
├── Security/         # Classes de sécurité
└── Service/          # Services métier

assets/
├── app.js            # Point d'entrée JavaScript
├── bootstrap.js      # Bootstrap
├── controllers/      # Stimulus controllers
│   ├── calendar_controller.js
│   ├── chatbot_controller.js
│   ├── hello_controller.js
│   └── voice_recognition_controller.js
└── styles/          # Feuilles de style

templates/
├── base.html.twig    # Template de base
├── BackOffice/       # Templates administrateur
├── FrontOffice/      # Templates front-end
├── emails/           # Templates d'emails
└── pdf/              # Templates PDF

machine_learning/
├── app.py            # Application ML
├── train_model.py    # Script d'entraînement
└── requirements.txt  # Dépendances Python

config/
├── packages/         # Configuration des bundles
└── routes/          # Définition des routes
```

##  Sécurité

L'application inclut :
- Authentification OAuth2 via KnpUniversity
- Contrôle d'accès basé sur les rôles (RBAC)
- Protection CSRF
- Validation des formulaires

##  Machine Learning

Pour entraîner les modèles :

```bash
cd machine_learning
pip install -r requirements.txt
python train_model.py
```

## Commandes utiles

```bash
# Lister les routes disponibles
php bin/console debug:router

# Afficher les services disponibles
php bin/console debug:container

# Exécuter les tests
php bin/phpunit

# Analyse statique du code
php bin/phpstan analyse

# Migration Doctrine
php bin/console doctrine:migrations:generate
php bin/console doctrine:migrations:execute --up VERSION
```

##  Controllers disponibles

- `calendar_controller` - Gestion du calendrier
- `chatbot_controller` - Chatbot IA
- `hello_controller` - Controller de démonstration
- `voice_recognition_controller` - Reconnaissance vocale

##  Configuration Email

L'application utilise Symfony Mailer. Les emails sont configurés dans `.env` :

```
MAILER_DSN=smtp://127.0.0.1:1025
```

En développement, accédez à MailHog : http://localhost:1025

##  Équipe de développement

- **Fares Lefi**
- **Mohamed Louay Charfi**
- **Yazid Kdhiri**
- **Moamed Ilyes Bizid**
- **Mohamed Amine Bondka**

