pkg_name=gatekeeper-app
pkg_origin=jarvus
pkg_version="0.1.0"
pkg_maintainer="Chris Alfano <chris@jarv.us>"
pkg_license=("AGPL-3.0")
pkg_deps=(
  emergence/php5
  jarvus/libfcgi
)


pkg_binds_optional=(
  [database]="port username password"
)

pkg_exports=(
  [port]=network.port
  [entrypoint]=entrypoint
)


do_build() {
  return 0
}

do_install() {
  build_line "Copying core..."
  cp -r "${PLAN_CONTEXT}/../../core" "${pkg_prefix}/core"

  build_line "Copying site..."
  cp -r "${PLAN_CONTEXT}/../../site" "${pkg_prefix}/site"
}

do_strip() {
  return 0
}