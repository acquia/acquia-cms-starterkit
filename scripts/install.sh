#!/usr/bin/env bash

# NAME
#     install.sh - Install CI dependencies
#
# SYNOPSIS
#     install.sh
#
# DESCRIPTION
#     Creates the test fixture.

cd "$(dirname "$0")"

# Reuse ORCA's own includes.
source ../../orca/bin/ci/_includes.sh

cd ${ORCA_FIXTURE_DIR}

# @todo Remove below after subrequests module issue is fixed.
# @see: https://www.drupal.org/project/subrequests/issues/3338312
composer config extra.patches.drupal/subrequests '{ "Incompatible with latest versions of symfony/http-foundation": "https://git.drupalcode.org/project/subrequests/-/merge_requests/15.patch" }' --json --merge

./vendor/bin/acms acms:install acquia_cms_headless --no-interaction --uri=http://127.0.0.1:8080

orca fixture:run-server &
sleep 8
#composer config -g github-oauth.github.com ${{ secrets.OAUTH_TOKEN }}

cat ${nextjs_app_env_file}
