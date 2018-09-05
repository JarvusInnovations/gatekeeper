pkg_name=gatekeeper-app
pkg_origin=jarvus
pkg_version="0.1.0"
pkg_maintainer="Chris Alfano <chris@jarv.us>"
pkg_license=("AGPL-3.0")
pkg_build_deps=(
  core/composer
  core/curl
)
pkg_deps=(
  emergence/php5
  jarvus/libfcgi
  core/git
)


pkg_binds=(
  [database]="port username password"
)

pkg_exports=(
  [port]=network.port
)


do_build() {
  return 0
}

do_install() {
  build_line "Loading core"

  mkdir "${pkg_prefix}/core"
  pushd "${pkg_prefix}/core" > /dev/null
  curl -L https://github.com/EmergencePlatform/php-core/tarball/master | tar xz --strip-components=1
  popd > /dev/null


  build_line "Copying site"

  mkdir "${pkg_prefix}/site"
  pushd "${PLAN_CONTEXT}/../../site" > /dev/null
  find . \
    -maxdepth 1 -mindepth 1 \
    -not -name '.git*' \
    -exec cp -r '{}' "${pkg_prefix}/site/{}" \;
  popd > /dev/null


  build_line "Running: composer install"
  pushd "${pkg_prefix}/core" > /dev/null
  COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev
  popd > /dev/null
}

do_strip() {
  return 0
}