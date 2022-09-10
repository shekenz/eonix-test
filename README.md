# eonix-test

This is my coding test (API part) for my interview at Eonix.be

## Usage

1. Check `service/.env.example`, edit the service port and save file as `service/.env` (Default port is 8200). You can also edit the docker containers name prefix. The compose file will run 2 containers with the names :

```
[prefix]-http # The nginx webserver
[prefix]-php  # The PHP-fpm container
```

**Note :** Database service is not included. You have to use your own external database service (See next step).

2. Check `.env.example` (at project's root), edit the database configuration to fit your environment, and save file as `.env`.

**Note :** The service should be able to create the database for you, if the user has the `CREATE` privilege.

3. Build and run the service container :

```docker-compose -f service/docker-compose.yml up -d```

4. Run `./composer install`. This will run the `composer` script located at the project's root. It is a shortcut for the composer provided inside the PHP container. You can also use your local composer if it is using PHP 8.1.

5. [Optional] Run the PHPUnit tests : `.phpunit`. This is also a shortcut script that will run the PHPUnit bin within the PHP container.

6. You should be able to access the api on `localhost:8200/` (Or whatever port you specified in step 1).