pkg_name=hololens-sass
pkg_origin=emergence
pkg_version="0.1.0"
pkg_maintainer="Chris Alfano <chris@jarv.us>"
pkg_license=("MIT")

pkg_deps=(
  core/git
  core/bash
  jarvus/compass
)

pkg_bin_dirs=(bin)


do_build() {
  return 0
}

do_install() {
  build_line "Generating lens script"

  pushd "${pkg_prefix}" > /dev/null
  cat > "bin/lens-tree" <<- EOM
#!$(pkg_path_for bash)/bin/sh

: \${HOLOLENS_INPUT?HOLOLENS_INPUT required}

# redirect all output to stderr
{
  # match disk to git tree
  git read-tree "\${HOLOLENS_INPUT}"
  git checkout-index --all --force
  git clean -df

  # execute compilation
  pushd "\${GIT_WORK_TREE}/sass" > /dev/null
  compass compile
  popd > /dev/null

  # add output to git index
  git add css
} 1>&2

# output tree hash
git write-tree --prefix=css

EOM
  chmod +x "bin/lens-tree"
  popd > /dev/null
}

do_strip() {
  return 0
}