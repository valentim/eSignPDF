#!/bin/sh

SECRET_NAME="eideasy"

INSTANCE_ID=$(curl -s http://169.254.169.254/latest/meta-data/instance-id)

export APP_URL=$INSTANCE_ID
export GOOGLE_REDIRECT_URI="$APP_URL/auth/google/callback"

SECRETS=$(aws secretsmanager get-secret-value --secret-id $SECRET_NAME --query SecretString --output text)

echo $SECRETS | jq -r 'to_entries | .[] | "export \(.key)=\(.value)"' > /tmp/env_vars.sh
source /tmp/env_vars.sh

exec "$@"