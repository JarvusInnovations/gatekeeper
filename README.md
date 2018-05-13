# gatekeeper-sandbox

## TODO

 - [X] Create services/http
    - [X] Provide guide/command to run in studio
 - [X] Rename services/php5 to services/app
    - [X] Use emergence/php5 as runtime dep instead of duplicating build plan
 - [X] Generate web.php with config
 - [ ] Get app working with minimal php-bootstrap changes
 - [X] Create composite service
   - [ ] Explore binding app and http services
 - [ ] Create composer package for core lib
   - [ ] Include PSR logger interface
   - [ ] Include whoops
   - [ ] Include VarDumper

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

## Potential improvements

- Set SCRIPT_FILENAME path dynamically from app package
  - Currently the http package derives it in order to set via nginx config
- Let app connect to mysql over specific IP without mysql being bound to 0.0.0.0 by user
