# gatekeeper-sandbox

## TODO

 - [X] Create services/http
    - [ ] Provide guide/command to run in studio
 - [X] Rename services/php5 to services/app
    - [X] Use emergence/php5 as runtime dep instead of duplicating build plan
 - [ ] Generate web.php with config
 - [ ] Get app working with minimal php-bootstrap changes
 - [X] Create composite service
   - [ ] Explore binding app and http services

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
