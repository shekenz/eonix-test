# eonix-test

This is my coding test (API part) for my interview at Eonix.be

## Usage

1. Check `service/.env.example`, edit the service port and save file as `service/.env` (Default port is 8200). You can also edit the docker containers name prefix. The compose file will run 2 containers with the names :

```
[prefix]-http # The nginx webserver
[prefix]-php  # The PHP-fpm container
```

**Note :** Database service is not included. You have to use your own external database service (See next step).

2. Check `.env.example`, edit the database configuration to fit your environment, and save file as `.env`.

**Note :** The service should be able to create the database for you, if the user has the `CREATE` privilege.

3. Build and run the service container :

```docker-compose -f service/docker-compose.yml up -d```

4. Run `composer install` (with PHP 8.1)

5. You should be able to access the api on `localhost:8200/` (Or whatever port you specified in step 1).