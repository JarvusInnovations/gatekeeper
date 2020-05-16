pkg_name=gatekeeper-netdata
pkg_origin=jarvus
pkg_version=1.17.0
pkg_maintainer="Chris Alfano <chris@jarv.us>"
pkg_license=("MIT")
pkg_build_deps=(
  jarvus/toml-merge
)
pkg_wrapped_ident="emergence/netdata"
pkg_deps=(
  core/bash
  "${pkg_wrapped_ident}"
)

pkg_exports=(
  [host]=server.address
  [port]=server.port
)
pkg_exposes=(port)
pkg_svc_run="netdata -D -c ${pkg_svc_config_path}/netdata.conf"

pkg_binds_optional=(
  [mysql]="port username password"
  [nginx]="port status_path"
  [phpfpm]="port status_path"
)

do_before() {
  push_runtime_env NETDATA_PKG_PLUGINS_DIR "${pkg_prefix}/plugins.d"
}

do_build() {
  return 0
}

do_install() {
  return 0
}

do_build_config() {
  do_default_build_config

  build_line "Merging config from ${pkg_wrapped_ident}"
  cp -nrv "$(pkg_path_for ${pkg_wrapped_ident})"/{config,hooks} "${pkg_prefix}/"
  toml-merge \
    "$(pkg_path_for ${pkg_wrapped_ident})/default.toml" \
    "${PLAN_CONTEXT}/default.toml" \
    > "${pkg_prefix}/default.toml"

  build_line "Copying local plugins.d"
  cp -rv "${PLAN_CONTEXT}/plugins.d" "${pkg_prefix}/"
  fix_interpreter "${pkg_prefix}/plugins.d/*" core/bash bin/bash
}

do_strip() {
  return 0
}
