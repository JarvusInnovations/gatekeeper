pkg_name=gatekeeper-app
pkg_origin=jarvus
pkg_version="0.1.0"
pkg_maintainer="Chris Alfano <chris@jarv.us>"
pkg_license=("AGPL-3.0")
pkg_deps=(
  emergence/php5
)

pkg_exports=(
  [port]=network.port
  [entrypoint]=entrypoint
)


do_build() {
  return 0
}

do_install() {
  mkdir "${pkg_prefix}/site"


  pushd "${PLAN_CONTEXT}/../../" > /dev/null

  build_line "Copying sites files into ${pkg_prefix}/site/"
  find . \
    -maxdepth 1 -mindepth 1 \
    -type d \
    -not -name 'services' \
    -not -name 'results' \
    -not -name '.git' \
    -exec cp -r '{}' "${pkg_prefix}/site/{}" \;

  popd > /dev/null


  return 0
}

do_strip() {
  return 0
}