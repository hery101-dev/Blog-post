# Blog - Résumé du projet

Ce dépôt est une démo Symfony + Bootstrap 5 avec plusieurs améliorations orientées UX / accessibilité.

## Fonctionnalités principales

- Thème clair / sombre (persisté) avec annonce pour lecteurs d'écran.
- Animation d'apparition (`reveal`) des sections (IntersectionObserver), respect de `prefers-reduced-motion`.
- Bouton « Retour en haut » accessible et animé.
- CRUD d'articles (create / read / update / delete) avec upload d'images et slugs automatiques.
- Recherche globale par titre (champ dans la barre de navigation).
- Recherche instantanée (autocomplete) : suggestions AJAX en temps réel (route `/ajax/search`).
- Translations partielles en français (labels et messages UX).

## Fichiers importants

- `templates/base.html.twig` — layout global, navbar, scripts (thème, reveal, autocomplete).
- `templates/home/index.html.twig` — page d'accueil et liste d'articles.
- `src/Repository/ArticleRepository.php` — méthodes de recherche.
- `src/Controller/HomeController.php` — affichage public & endpoint AJAX.
- `assets/styles/app.css` — variables de thème, styles et animations.

## Démarrage rapide

1. `composer install`
2. `php bin/console doctrine:migrations:migrate`
3. `php bin/console doctrine:fixtures:load --no-interaction` (optionnel)
4. `symfony server:start`
5. Ouvrir `http://localhost:8000`

## Test rapide de l'autocomplete

- Ouvre la barre de recherche, commence à taper un titre — les suggestions apparaissent sans valider.
- Utilise flèches / Entrée pour sélectionner une suggestion et l'ouvrir.


---

## Réutiliser le projet depuis GitHub (cloner / configurer)

Si vous publiez ce dépôt sur GitHub et que vous voulez le cloner sur une autre machine, suivez ces étapes (en français) :

1. Cloner le dépôt et se placer dans le dossier :

```bash
git clone https://github.com/hery101-dev/Blog-post.git
cd VOTRE-REPO
```

2. Installer les dépendances PHP :

```bash
composer install
```

3. Configurer les variables d'environnement :

- Copier le fichier d'exemple `.env` en `.env.local` et adapter `DATABASE_URL`, `APP_SECRET`, etc. (`.env.local` n'est pas commité).

```bash
cp .env .env.local
# éditer .env.local et renseigner DATABASE_URL
```

4. Créer la base de données et exécuter les migrations :

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

5. (Optionnel) Charger des données de démonstration :

```bash
php bin/console doctrine:fixtures:load --no-interaction
```

6. Préparer les assets (si vous utilisez un builder JS/CSS) :

- Si vous avez des dépendances Node (ex : Vite, Webpack Encore), installez et build :

```bash
npm install
npm run build
```

7. Créer le dossier des uploads et vérifier les permissions :

```bash
mkdir -p public/images
# Sous Linux : chown/chmod si nécessaire (ex : chown -R www-data:www-data public/images)
```

8. Démarrer l'application :

```bash
symfony server:start
# ou : php -S localhost:8000 -t public
```

9. Ouvrir dans le navigateur : `http://localhost:8000` et tester la recherche instantanée depuis la barre de navigation.

Notes :

- `.env.local` ne doit pas être poussé vers Git (il contient des secrets). Si vous déployez sur un hôte ou CI, configurez les variables d'environnement appropriées (APP_ENV, DATABASE_URL, APP_SECRET, etc.).
- Pour une installation en production, utilisez `composer install --no-dev` et configurez un serveur HTTP + PHP-FPM.
- Si vous utilisez Docker / Docker Compose, adaptez les étapes pour votre environnement (fichiers `docker-compose` éventuels).
