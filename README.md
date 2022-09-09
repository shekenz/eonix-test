# eonix-test

This is my coding test (API part) for my interview at Eonix.be

## Usage

1. Check `service/.env.example`, edit the service port and save file as `service/.env` (Default port is 8200).

3. Check `.env.example`, edit the database configuration to fit your environment, and save file as `.env`.

3. Build and run the service container :

```docker-compose -f service/docker-compose.yml up -d```

4. You should be able to access the api on `localhost:8200/` (Or whatever port you specified in step 1).