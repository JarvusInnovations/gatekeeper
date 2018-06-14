pkg_name=gatekeeper-http
pkg_origin=jarvus
pkg_version="0.1.0"
pkg_maintainer="Chris Alfano <chris@jarv.us>"
pkg_license=("AGPL-3.0")
pkg_deps=(
  emergence/nginx
)


pkg_binds=(
  [app]="port"
)

pkg_exports=(
  [port]=http.listen.port
)

pkg_exposes=(port)


do_build() {
  return 0
}

do_install() {
  return 0
}

do_build_config() {
  do_default_build_config || return $?

  cp -vnr "$(pkg_path_for emergence/nginx)/config"/*.include "${pkg_prefix}/config/"
}
