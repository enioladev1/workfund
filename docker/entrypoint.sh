#!/bin/sh
set -e

# --force only skips the interactive "run in production?" prompt; it never
# drops or resets data, so this is safe to run on every boot/redeploy.
php artisan migrate --force

# Only seed on a genuinely empty database, so redeploys never duplicate or
# reset the demo customers/orders.
if [ "$(php artisan tinker --execute='echo \App\Models\Customer::query()->count();' 2>/dev/null | tail -n 1)" = "0" ]; then
    php artisan db:seed --force
fi

exec "$@"
