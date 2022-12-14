# eonix-test

This is my coding test (API part) for my interview at Eonix.be

### Installation

1. Check `service/.env.example`, edit the service port and save file as `service/.env` (Default port is 8200). You can also edit the docker containers name prefix. The compose file will run 2 containers with the names :
    ```
    [prefix]-http # The nginx webserver
    [prefix]-php  # The PHP-FPM container
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

| Endpoint | Method | Description | Accepts data |
| --- | --- | --- | --- |
| `/user/create` | PUT | Create new user | Yes |
| `/user/[id]` | POST | Get user by ID | No |
| `/users` | POST | List users with filters | Yes |
| `/user/update/[id]` | PATCH | Update user's data | Yes |
| `/user/delete/[id]` | DELETE | Delete user | No |

**Notes :**
- All requests need to have the Content-Type header `application/json`.
- All requests need a valid JSON body, even endpoints that don't accept data. A minimal body should be at least an empty JSON string `{}`.
- User's ID is an 16 bytes hexadecimal string (32 characters). Anything different will be rejected.
- Data structure is the same for all requests, weather you want to create, filter or update an user. The JSON should look like this :
``` js
{
    "firstname": "John",
    "lastname": "Doe"
}
```

### Development notes (French)

##### A propos de mes choix de d??veloppement :

- Compte tenu du fait que l'exercice ne reposait que sur une seule ressource, je n'ai pas trouv?? pertinent d'utiliser un framework MVC complet avec ORM. Cela me semblait trop lourd. J'ai donc choisit de limiter l'utilisation de librairies a seulement quelques utilitaires. Cela m'a permis de d??velopper mon propre mini-framework afin de montrer mes connaissances en PHP et SQL.
- Le service est d??ployable via Docker. Il contient un Dockerfile pour construire l'image du container PHP, et utilise l'image `nginx:fpm-alpine` pour servir l'API. Vous trouverez les racourcis `./phpunit` et `./composer` qui utilisent directement la version de PHP containeuris??e.
- Le service tentera de cr??er la base de donn??e et la table 'user' en cas d'absence. Cela donne plus de r??silience au service. Il vous suffira de fournir les donn??es de connexion du server MySQL/MariaDB avec un utilisateur poss??dant le privil??ge `CREATE` et vous devriez pouvoir utiliser le service directement.
- L'exercice demandant sp??cifiquement d'enregistrer les utilisateurs sous un GUID, j'ai choisit BINARY(16) comme type de donn??es afin d'optimiser l'indexation.
- Le controller `Users` combine en r??alit?? la logique Controller et Model afin de simplifier le d??veloppement.
- Je n'ai volontairement pas utilis?? les fonctions d'output buffer de PHP dans ma classe de rendu (View) afin de ne pas g??n??rer des signalements "risky" dans PHPUnit (certaines m??thodent ouvrent un buffer qui sera referm?? ensuite par la classe de rendu, ce que PHPUnit n'apr??ciait pas lors des tests)
- Le shema de donn??e JSON est toujours le m??me, que ce soit pour cr??er, mettre ?? jour ou filtrer les utilisateurs : un tableau associatifs contenant les cl??s `firstname` et `lastname`, encore une fois dans une logique de simplification et d'efficacit??.
- Le projet comporte 48 tests qui permettent de v??rifier que tout fonctionne bien comme il se doit.

##### Ce que j'aurai aim?? ajouter (avec plus de temps) :

- Une m??thode sp??cifique `queryBuilder` dans le controller pour fa??onner les requ??tes SQL de fa??on plus universelles.
- Une m??thode `validator`dans le controller pour valider plus efficacement et plus g??n??ralement les donn??es envoy??es par le client.
- Une m??thode pour ajouter des utilisateurs par lot (Commenc??e mais avort??e).