# Eventizer Backend

Version actuelle de laravel est 6.

## Init

1. `composer install`
2. `php artisan migrate`
3. `php artisan db:seed`

## Lancement

`php artisan serve --port=8888`
## Unit Tests

### Init

1. `php artisan migrate --env=testing`

2. `php artisan db:seed --env=testing`

### Lancement

`./vendor/bin/phpunit` (à prévoir de modifier cette commande avec `Laravel 7`)

### Comment ca marche ?

Tous les scénarios de tests sont décrit dans le dossier `/tests`

Tous les tests hérite du classe `TestCase.php` qui contient les initialisations nécaissaire pour chaque scénario.

#### Créer un nouveau test

**1. Création du fonction:**
* Si le domaine d'application déja créer on ajoute une nouvelle fonction dans l'un des fichiers dans `Features`

* Sinon créer une nouvelle fichier avec le nommage `DomaineTest.php`. example `UserTest.php`

**2. Création du dataSet**

Les data sets sert à ajouter des données fake qu'on est besoin lors de tests. Ce sont des tests unitaires donc on va tester unitairement un WS et pas des WS ensemble dans le méme fonction.

Pour créer un dataSet il suffit d'aller vers `database/factories` et créer une nouvelle classe avec le nommage `DomaineFactory.php`



