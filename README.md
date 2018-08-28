# Football API

This is a test project for to create a REST API with JWT authentication.

## Setup

Clone or download the project in a folder and then install dependencies using composer:

```bash
    $ cd PROJECT_FOLDER
    $ composer install
```

Populate database with some example records using the next command:

```bash
    $ cd PROJECT_FOLDER
    $ php bin/console football:import-teams teams.csv
```

> Notice: For to run Postman tests I don't recommend you modify the file **teams.csv**.

Next run a local web server using the next command:

```bash
    $ cd PROJECT_FOLDER
    $ php bin/console server:run
```

## Tests

You can execute the tests running the next command:

```bash
    $ cd PROJECT_FOLDER
    $ ./vendor/bin/simple-phpunit
```

> Notice: PHPUNIT tests don't require records in the database because I've mocked up the Entity Manager.

## API endpoints

There are 4 endpoints:

- **GET** /api/leagues/{id} - Returns a league with his teams.
- **DELETE** /api/leagues/{id} - Deletes a league and his teams.
- **POST** /api/leagues/{leagues_id}/teams - Create a new team in the given league.
- **PUT** /api/leagues/{league_id}/teams/{team_id} - Update a team.

## Postman

You will find examples of all the endpoints in the next url [Postman online documentation](https://documenter.getpostman.com/view/41018/RWTsrFqJ)

I've attached in the project 3 Postman collections that you can import to your Postman app:

- **Football_API.postman_collection_v1.json** - For Postman V1
- **Football_API.postman_collection_v2.json** - For Postman V2
- **Football_API.postman_collection_v2.1.json** - For Postman V2.1

## Token secret

There are two **token_secret** located at...

`app/config/config.yml` - This is for production and dev environments.
 
`app/config/config_test.yml` - This is for to run the unit tests.

> Notice: If you modify the **token_secret** for test environment all the tests will fail.

## Authors

* **Jose Antonio** - *Initial work*

## Donations

If you found this useful. Please, consider support with a small donation:

* **BTC** - 1PPn4qvCQ1gRGFsFnpkufQAZHhJRoGo2g5
* **BCH** - qr66rzdwlcpefqemkywmfze9pf80kwue0v2gsfxr9m
* **ETH** - 0x5022cf2945604CDE2887068EE46608ed6B57cED8

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details