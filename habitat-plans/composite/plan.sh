pkg_name=gatekeeper
pkg_origin=jarvus
pkg_type=composite
pkg_version="0.1.0"
pkg_maintainer="Chris Alfano <chris@jarv.us>"
pkg_license=("AGPL-3.0")

pkg_services=(
    "${HAB_ORIGIN}/gatekeeper-app"
    emergence/nginx
)

pkg_bind_map=(
    [emergence/nginx]="app:${HAB_ORIGIN}/gatekeeper-app"
)
