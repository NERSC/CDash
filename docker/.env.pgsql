APP_NAME=CDash
APP_ENV=testing
APP_KEY=base64:/4agNXrZCKuMNyJm9lUSLXNFjASyr9xJi+mJE1ece3k=
APP_DEBUG=true
APP_URL=http://localhost
APP_TIMEZONE=America/New_York
LOG_CHANNEL=stack

COMPOSE_PROJECT_NAME=cdash

DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=cdash4simpletest
DB_USERNAME=postgres
DB_PASSWORD=cdash4simpletest

BROADCAST_DRIVER=log
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_DRIVER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_APP_CLUSTER=mt1

MIX_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
MIX_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"