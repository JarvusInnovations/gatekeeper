composite_app_pkg_name=gatekeeper
pkg_name="${composite_app_pkg_name}-composite"
pkg_origin=jarvus
pkg_maintainer="Jarvus Innovations <hello@jarv.us>"
pkg_scaffolding=emergence/scaffolding-composite
composite_mysql_pkg=core/mysql

pkg_version() {
  scaffolding_detect_pkg_version
}
