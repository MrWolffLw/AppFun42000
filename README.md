# Vitrine d'applications (Symfony 7.4)

Site web pour présenter vos applications, avec une **administration** pour ajouter, pour chaque application : une description (Markdown), une icône, une image de couverture, des **captures d'écran** (envoi multiple), des **vidéos** (lien YouTube/Vimeo ou fichier MP4/WebM), des légendes, et choisir l'ordre d'affichage.

> **Statut : code écrit mais jamais exécuté.** Il a été produit sans PHP ni Composer à disposition : seuls le YAML, le JavaScript et l'équilibre des fichiers ont été vérifiés. Faites les contrôles de la section « Premier démarrage » avant tout déploiement ; si une commande signale une erreur, elle indique la ligne à corriger.

## Ce que contient le site

**Public** : accueil (liste des applications), page par application (présentation, points forts, galerie d'images avec visionneuse, vidéos, liens Google Play / App Store / site, autres applications), À propos, Contact (avec antispam), Mentions légales, Confidentialité, `sitemap.xml`, `robots.txt`, balises de partage (Open Graph) et données structurées pour les moteurs de recherche. Thème clair/sombre automatique, adapté au téléphone.

**Administration** (`/admin`, connexion sur `/login`) : tableau de bord, applications (créer, modifier, publier/masquer, réordonner, supprimer), médias par application (ajout d'images en lot, ajout de vidéos, légendes, réordonnancement, suppression), messages reçus, réglages du site (nom, titres, textes des pages À propos / légales / confidentialité).

## Prérequis

PHP **8.2 ou plus** avec les extensions `ctype`, `iconv`, `fileinfo`, `mbstring`, `xml`, `pdo_sqlite` (ou `pdo_mysql` / `pdo_pgsql`), et [Composer](https://getcomposer.org).

## Installation

```bash
composer install
cp .env .env.local                       # puis modifiez APP_SECRET dans .env.local
php -r "echo bin2hex(random_bytes(16)), PHP_EOL;"   # valeur à mettre dans APP_SECRET

php bin/console doctrine:schema:create   # crée les tables (SQLite par défaut : var/data.db)
php bin/console app:create-admin vous@exemple.fr    # crée votre compte (mot de passe : 12 caractères minimum)

php -d upload_max_filesize=256M -d post_max_size=270M -S 127.0.0.1:8000 -t public
```
Ouvrez http://127.0.0.1:8000/login, connectez-vous, puis **Applications > Ajouter une application**.

Pour MySQL ou PostgreSQL, changez `DATABASE_URL` dans `.env.local` (exemples dans `.env`). Pour suivre les évolutions du schéma en production, préférez les migrations : `php bin/console doctrine:migrations:diff` puis `doctrine:migrations:migrate`.

## Premier démarrage : contrôles à lancer

```bash
php bin/console lint:yaml config
php bin/console lint:twig templates
php bin/console lint:container
php bin/console debug:router
php bin/console doctrine:schema:validate
vendor/bin/phpunit
```
Ces commandes détectent la plupart des erreurs de configuration, de gabarit ou de mappage avant la première visite.

## Ajouter une application, pas à pas

1. **Applications > Ajouter** : saisissez le nom, l'accroche, la description courte (aperçus de partage) et la description complète en Markdown (`## Titre`, `**gras**`, `- liste`, `[lien](https://…)`). Le HTML brut est volontairement ignoré.
2. Listez les **points forts** (un par ligne), choisissez la **couleur** de l'application, envoyez l'**icône** et l'**image de couverture**, ajoutez les liens des boutiques.
3. Cochez **Publiée** quand vous êtes prêt (un brouillon reste visible pour vous seul).
4. Après l'enregistrement, la section **Images et vidéos** apparaît :
   - **Images** : sélectionnez plusieurs fichiers d'un coup (JPEG, PNG, WebP, 8 Mo maximum chacun) ;
   - **Vidéos** : collez un lien YouTube ou Vimeo, **ou** envoyez un fichier MP4/WebM (256 Mo maximum) ;
   - modifiez la **légende**, changez l'**ordre** (↑ ↓) ou **supprimez** chaque média.

## Limites d'envoi de fichiers

Trois réglages doivent être cohérents : `upload_max_filesize` et `post_max_size` (PHP, déjà dans `public/.user.ini` pour PHP-FPM) et `client_max_body_size` (nginx). Si un envoi semble ignoré sans message d'erreur, c'est presque toujours une limite du serveur trop basse. Pour de longues vidéos, préférez YouTube/Vimeo.

## Mise en production

1. `.env.local` : `APP_ENV=prod`, un `APP_SECRET` aléatoire, `DATABASE_URL`, `MAILER_DSN` (par exemple SMTP), `CONTACT_TO`, `CONTACT_FROM`.
2. `composer install --no-dev --optimize-autoloader` puis `php bin/console cache:clear`.
3. Créez la base (schéma ou migrations), puis `app:create-admin`.
4. Le serveur web doit pointer sur `public/` et **PHP doit pouvoir écrire** dans `var/` et `public/uploads/`.
5. Servez le site en **HTTPS**. Derrière un proxy, déclarez-le avec `framework.trusted_proxies`.
6. **Sauvegardez** régulièrement la base de données et le dossier `public/uploads/` (sans lui, les images et vidéos sont perdues).

Exemple nginx :
```nginx
server {
    server_name exemple.fr;
    root /var/www/vitrine/public;
    client_max_body_size 270M;

    location / { try_files $uri /index.php$is_args$args; }
    location ~ ^/index\.php(/|$) {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
        internal;
    }
    location ~ \.php$ { return 404; }
    location ~ /\. { deny all; }
}
```

## Sécurité : ce qui est en place

- Administration protégée par connexion (mots de passe hachés, limitation à 5 tentatives, jeton CSRF) ; toutes les actions d'écriture sont en POST avec jeton CSRF.
- Fichiers envoyés : type vérifié sur le **contenu**, nom aléatoire, extension déduite du contenu, SVG refusé, exécution de scripts interdite dans `public/uploads/` (`.htaccess` fourni ; règle nginx ci-dessus).
- Texte Markdown : HTML brut supprimé, liens `javascript:` refusés (testé dans `MarkdownRendererTest`).
- Liens saisis : seuls `http` et `https` acceptés.
- En-têtes : CSP restrictive (seuls YouTube-nocookie et Vimeo autorisés en cadres), `nosniff`, `X-Frame-Options`, `Referrer-Policy`.
- Contact : piège à robots invisible, limite de 5 messages par heure et par adresse IP.
- Vidéos intégrées en mode « sans cookies » et **chargées uniquement au clic** : aucune requête vers un tiers avant que le visiteur le décide.

## Vie privée (RGPD)

Pas de statistiques ni de traceurs, polices du système (aucune requête vers Google Fonts). Un cookie de session technique n'apparaît que sur le formulaire de contact et l'administration. Complétez vos pages Mentions légales et Confidentialité dans **Réglages du site** (des modèles à compléter sont préremplis).

## Personnaliser le design

Les couleurs, la typographie et l'espacement sont des variables en tête de `public/assets/site.css`. Chaque application a sa propre couleur d'accent (choisie dans l'administration) ; préférez une couleur assez foncée pour que le texte blanc des boutons reste lisible. Les polices sont celles du système : pour une police personnalisée, hébergez-la vous-même dans `public/assets/` plutôt que de passer par un service tiers.

## Structure

```
config/            configuration (framework, sécurité, base de données, services)
src/Entity/        Application, Media, ContactMessage, SiteSetting, User
src/Controller/    pages publiques ; Admin/ : tableau de bord, applications, médias, réglages, messages
src/Form/          formulaires (application, images, vidéo, contact, réglages)
src/Service/       FileUploader, VideoUrlParser, MarkdownRenderer
templates/         gabarits Twig (site, admin/, erreurs)
public/assets/     site.css, admin.css, site.js, admin.js
public/uploads/    fichiers envoyés (icons, covers, images, videos)
tests/             tests unitaires (VideoUrlParser, MarkdownRenderer)
```

## Limites connues et pistes d'évolution

- Pas de miniatures générées : les images sont servies telles qu'envoyées. Envoyez des fichiers raisonnablement légers (ou ajoutez un redimensionnement, par exemple avec LiipImagineBundle).
- Site en français uniquement.
- Les liens de vidéos autres que YouTube et Vimeo ne sont pas acceptés (envoyez un fichier MP4/WebM).
- Pas de test fonctionnel automatisé des pages et de l'administration : à ajouter une fois le projet installé (`symfony/test-pack`).
- Les versions des dépendances (Symfony 7.4, Doctrine ORM 3) ont été choisies sans pouvoir lancer `composer install` : ajustez `composer.json` si Composer signale un conflit.
# AppFun42000
