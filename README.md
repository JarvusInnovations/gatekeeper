# gatekeeper-sandbox

## Studio Development

1. Install habitat

    ```bash
    curl -s https://raw.githubusercontent.com/habitat-sh/habitat/master/components/hab/install.sh | sudo bash
    ```

1. Set up habitat

    When prompted, enter `slate` as your default origin and choose yes to generate a key

    ```bash
    hab setup
    ```

1. Launch studio with port 7080 mapped to host:

    ```bash
    HAB_DOCKER_OPTS="-p 7080:7080" hab studio enter
    ```

1. Build all applications within studio:

    ```bash
    build-all
    ```

1. Launch all applications with default test configuration:

    ```bash
    start-all
    ```

## Working with Docker

1. Launch studio:

    ```bash
    hab studio enter
    ```

1. Build app package and export docker container

    ```bash
    build services/app
    hab pkg export docker $HAB_ORIGIN/gatekeeper-app
    ```

1. Build http package and export docker container

    ```bash
    build services/http
    hab pkg export docker $HAB_ORIGIN/gatekeeper-http
    ```

1. Export docker container for mysql

    - Local mysql:

      ```bash
      hab pkg export docker core/mysql
      ```

    - Remote mysql:

      ```bash
      hab pkg export docker jarvus/mysql-remote
      ```

1. Exit studio

    ```bash
    exit
    ```

1. Launch containers with docker-compose

    - Local mysql:

      ```bash
      docker-compose -f docker-compose.mysql-local.yml up
      ```

    - Remote mysql:

      ```bash
      docker-compose -f docker-compose.mysql-remote.yml up
      ```

### Helpful Commands

- Launch interactive bash shell for any service container defined in `docker-compose.*.yml`:

    ```bash
    SERVICE_NAME=app
    MYSQL_MODE=local
    docker-compose -f docker-compose.mysql-${MYSQL_MODE}.yml exec ${SERVICE_NAME} hab sup bash
    ```

- Access interactive mysql shell:

  - Local mysql:

    ```bash
    docker-compose -f docker-compose.mysql-local.yml \
      exec db \
      mysql \
        --defaults-extra-file=/hab/svc/mysql/config/client.cnf \
        gatekeeper
    ```

  - Remote mysql:

    ```bash
    docker-compose -f docker-compose.mysql-remote.yml \
      exec db \
      hab pkg exec core/mysql-client mysql \
        --defaults-extra-file=/hab/svc/mysql-remote/config/client.cnf \
        gatekeeper
    ```

- Promote access level for registered user:

  - Local mysql:

    ```bash
    docker-compose -f docker-compose.mysql-local.yml \
      exec db \
      mysql \
        --defaults-extra-file=/hab/svc/mysql/config/client.cnf \
        gatekeeper \
        -e 'UPDATE people SET AccountLevel = "Developer" WHERE Username = "chris"'
    ```

  - Remote mysql:

    ```bash
    docker-compose -f docker-compose.mysql-remote.yml \
      exec db \
      hab pkg exec core/mysql-client mysql \
        --defaults-extra-file=/hab/svc/mysql-remote/config/client.cnf \
        gatekeeper \
        -e 'UPDATE people SET AccountLevel = "Developer" WHERE Username = "chris"'
    ```

## TODO

- [X] Create services/http
  - [X] Provide guide/command to run in studio
- [X] Rename services/php5 to services/app
  - [X] Use emergence/php5 as runtime dep instead of duplicating build plan
- [X] Generate web.php with config
- [ ] Get app working with minimal php-bootstrap changes
- [X] Create composite service
  - [X] Explore binding app and http services
- [X] Create composer package for core lib
  - [ ] Include PSR logger interface
  - [X] Include whoops
  - [X] Include VarDumper
- [X] Clear ext frameworks from build
- [ ] Add postfix service
- [ ] Add fcgi health check for status url if available
- [ ] Add cron job for app heartbeat event

## Journal

- Create http plan
  - Use existing plan as runtime dep
  - Copy configs from existing plan during build and skip build/install
  - Set paths in templated conf
  - nginx dieing with `open() "/dev/stdout" failed (13: Permission denied)`
    - add config for log paths
  - Test process
    - Build custom nginx
    - Install custom nginx in app studio
    - Build app http plan
    - Stop/start service
    - -- config-from useful when testing just changes to app nginx config, but build needed to update nginx version after install
  - set port at `/hab/user/gatekeeper-http/config/user.toml`
- Create app plan
  - Use existing plan as runtime dep
- Connect with binding
- Create composite plan
  - Add binding
- Create mysql plan
- Write `/hab/user` via `.studiorc`
- Configure composer and psysh for studio
- Create package for libfcgi and use with ping for FPM application health_check

## Potential improvements

- Set SCRIPT_FILENAME path dynamically from app package
  - Currently the http package derives it in order to set via nginx config
- Let app connect to mysql over specific IP without mysql being bound to 0.0.0.0 by user
