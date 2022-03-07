# Eventizer Backend

Version actuelle de laravel est 8.

## Init

1. `composer install`
2. `php artisan migrate` 
3. `php artisan db:seed`

## Lancement

`php artisan serve --port=8888`
## Unit Tests

### Init

1.  Créer un fichier `.env.testing` en mettant `APP_ENV=testing` et un nom de base `eventizer_test`
2.  Dans votre local créer une base `eventizer_test`
3. `php artisan migrate --env=testing`
4. `php artisan db:seed --env=testing`

### Lancement

`php artisan test`

### Comment ca marche ?

Tous les scénarios de tests sont décrit dans le dossier `/tests`

Tous les tests héritent du classe `TestCase.php` qui contient les initialisations nécaissaires pour chaque scénario.

#### Créer un nouveau test

**1. Création du fonction:**
* Si le domaine d'application déja créer on ajoute une nouvelle fonction dans l'un des fichiers dans `Features`

* Sinon créer un nouveau fichier avec le nommage `DomaineTest.php`. example `UserTest.php`

**2. Création du dataSet**

Les data sets sert à ajouter des données fake qu'on est besoin lors de tests. Ce sont des tests unitaires donc on va tester unitairement un WS et pas des WS ensemble dans le méme fonction.

Pour créer un dataSet il suffit d'aller vers `database/factories` et créer une nouvelle classe avec le nommage `DomaineFactory.php`



MAIL_DRIVER=SMTP
MAIL_HOST=smtp.gmail.com
MAIL_PORT=465
MAIL_USERNAME=(email)
MAIL_PASSWORD=(mot de passe)
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=(email)
MAIL_FROM_NAME="Plateforme Eventizer"