#!/usr/bin/env bash

set -euo pipefail

project_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$project_dir"

GITHUB_PAGES=true NEXT_PUBLIC_BASE_PATH= pnpm build:pages
cp deployment/cpanel/.htaccess out/.htaccess

printf '%s\n' "cPanel deployment files are ready in $project_dir/out"
