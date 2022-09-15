# eonix-test

This is my coding test (API part) for my interview at Eonix.be

### Installation

1. Check `service/.env.example`, edit the service port and save file as `service/.env` (Default port is 8200). You can also edit the docker containers name prefix. The compose file will run 2 containers with the names :
```
[prefix]-http # The nginx webserver
[prefix]-php  # The PHP-fpm container
```
**Note :** Database service is not included. You have to use your own external **MySQL/MariaDB** service.

2. Check `.env.example` (at project's root), edit the database configuration to fit your environment, and save file as `.env`.

**Note :** The service should be able to create the database for you, if the user has the `CREATE` privilege.

3. Build and run the service container :

```
docker-compose -f service/docker-compose.yml up -d
```

4. Run `./composer install`. This will run the `composer` script located at the project's root. It is a shortcut for the composer provided inside the PHP container. You can also use your local composer if it is using PHP 8.1.

5. [Optional] Run the PHPUnit tests : `.phpunit`. This is also a shortcut script that will run the PHPUnit bin within the PHP container. Database might be altered in case of failing test. Use on a test database.

6. You should be able to access the api on `localhost:8200/` (Or whatever port you specified in step 1). I recomand using Postman to play with the service.

### Use

- **Create a new user** : `PUT` application/json request to `localhost:8200/user/create` with data :
``` js
{
    "firstname": "John",
    "lastname": "Doe"
}
```
Server responds with the created user as application/json.

- **Get a specific user** : `POST` application/json request to `localhost:8200/user/[id]` where id is hexadecimal 16bytes GUID. Server responds with the selected user as application/json.

- **Get all users** : `POST` application/json request to `localhost:8200/users`. You can eventually pass filtering data :
``` js
{
    "firstname": "Jo",
    "lastname": "oe"
}
```
Server responds with an array of users as application/json.

- **Update an user** : `PATCH` application/json request to `localhost:8200/user/update/[id]` where id is hexadecimal 16bytes GUID. Pass data to update :
``` js
{
    "firstname": "New firstname",
    "lastname": "New lastname"
}
```
Server responds with the updated user as application/json.

- **Delete an user** : `DELETE` application/json request to `localhost:8200/user/delete/[id]` where id is hexadecimal 16bytes GUID. Server responds with a 200 or 404.

### Development notes (French)

##### A propos de mes choix de développement :

- Compte tenu du fait que l'exercice ne reposait que sur une seule ressource, je n'ai pas trouvé pertinent d'utiliser un framework MVC complet avec ORM. Cela me semblait trop lourd. J'ai donc choisit de limiter l'utilisation de librairies a seulement quelques utilitaires. Cela m'a permis de développer mon propre mini-framework afin de montrer mes connaissances en PHP et SQL.
- Le service est déployable via Docker. Il contient un Dockerfile pour construire l'image du container PHP, et utilise l'image `nginx:fpm-alpine` pour servir l'API. Vous trouverez les racourcis `./phpunit` et `./composer` qui utilisent directement la version de PHP containeurisée.
- Le service tentera de créer la base de donnée et la table 'user' en cas d'absence. Cela donne plus de résilience au service. Il vous suffira de fournir les données de connexion du server MySQL/MariaDB avec un utilisateur possédant le privilège `CREATE` et vous devriez pouvoir utiliser le service directement.
- L'exercice demandant spécifiquement d'enregistrer les utilisateurs sous un GUID, j'ai choisit BINARY(16) comme type de données afin d'optimiser l'indexation.
- Le controller `Users` combine en réalité la logique Controller et Model afin de simplifier le développement.
- Je n'ai volontairement pas utilisé les fonctions d'output buffer de PHP dans ma classe de rendu (View) afin de ne pas générer des signalements "risky" dans PHPUnit (certaines méthodent ouvrent un buffer qui sera refermé ensuite par la classe de rendu, ce que PHPUnit n'apréciait pas lors des tests)
- Le shema de donnée JSON est toujours le même, que ce soit pour créer, mettre à jour ou filtrer les utilisateurs : un tableau associatifs contenant les clés `firstname` et `lastname`, encore une fois dans une logique de simplification et d'efficacité.

##### Ce que j'aurai aimé ajouter (avec plus de temps) :

- Une méthode spécifique `queryBuilder` dans le controller pour façonner les requêtes SQL de façon plus universelles.
- Une méthode `validator`dans le controller pour valider plus efficacement et plus généralement les données envoyées par le client.
- Une méthode pour ajouter des utilisateurs par lot (Commencée mais avortée).