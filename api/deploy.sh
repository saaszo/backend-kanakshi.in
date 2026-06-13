#!/usr/bin/env bash
set -euo pipefail

: "${HOSTINGER_HOST:?Set HOSTINGER_HOST}"
: "${HOSTINGER_PORT:?Set HOSTINGER_PORT}"
: "${HOSTINGER_USER:?Set HOSTINGER_USER}"
: "${HOSTINGER_APP_PATH:?Set HOSTINGER_APP_PATH}"
: "${HOSTINGER_SSH_KEY:?Set HOSTINGER_SSH_KEY}"

ssh_key_file="$(mktemp)"
trap 'rm -f "$ssh_key_file"' EXIT

printf '%s\n' "$HOSTINGER_SSH_KEY" > "$ssh_key_file"
chmod 600 "$ssh_key_file"

rsync -avz \
  --exclude=".env" \
  --exclude=".git" \
  --exclude="node_modules" \
  --exclude="vendor" \
  --exclude="database/database.sqlite" \
  --exclude="storage/" \
  --exclude="bootstrap/cache/" \
  -e "ssh -o StrictHostKeyChecking=no -p ${HOSTINGER_PORT} -i ${ssh_key_file}" \
  "./" "${HOSTINGER_USER}@${HOSTINGER_HOST}:${HOSTINGER_APP_PATH}/"
