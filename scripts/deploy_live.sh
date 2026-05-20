#!/usr/bin/env bash
set -euo pipefail

# Deploy local acp_demo_live project to production with safety steps:
# 1) backup, 2) rsync dry-run, 3) deploy, 4) ownership fix, 5) smoke check.

REMOTE_USER="${REMOTE_USER:-root}"
REMOTE_HOST="${REMOTE_HOST:-obadiah.scee.edu.au}"
REMOTE_PORT="${REMOTE_PORT:-22}"
REMOTE_PATH="${REMOTE_PATH:-/home/convention/public_html/acp_demo}"
REMOTE_OWNER="${REMOTE_OWNER:-convention:convention}"
SMOKE_URL="${SMOKE_URL:-https://convention.accelerateministries.com.au/acp_demo/}"

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
LOCAL_PATH="${LOCAL_PATH:-$(cd "${SCRIPT_DIR}/.." && pwd)/}"

RUN_BACKUP=1
AUTO_APPROVE=0
DRY_RUN_ONLY=0

usage() {
  cat <<'EOF'
Usage:
  ./scripts/deploy_live.sh [options]

Options:
  --dry-run-only    Show rsync changes only; do not deploy.
  --skip-backup     Skip remote tar backup step.
  --yes             Do not prompt before live deploy.
  --help            Show this help.

Environment overrides:
  REMOTE_USER, REMOTE_HOST, REMOTE_PORT, REMOTE_PATH, REMOTE_OWNER,
  SMOKE_URL, LOCAL_PATH
EOF
}

while [[ $# -gt 0 ]]; do
  case "$1" in
    --dry-run-only)
      DRY_RUN_ONLY=1
      shift
      ;;
    --skip-backup)
      RUN_BACKUP=0
      shift
      ;;
    --yes)
      AUTO_APPROVE=1
      shift
      ;;
    --help|-h)
      usage
      exit 0
      ;;
    *)
      echo "Unknown option: $1" >&2
      usage
      exit 1
      ;;
  esac
done

if [[ ! -d "$LOCAL_PATH" ]]; then
  echo "Local path does not exist: $LOCAL_PATH" >&2
  exit 1
fi

if [[ "${LOCAL_PATH}" != */ ]]; then
  LOCAL_PATH="${LOCAL_PATH}/"
fi

RSYNC_EXCLUDES=(
  --exclude='.git'
  --exclude='vendor/'
  --exclude='tmp/'
  --exclude='logs/'
  --exclude='webroot/files/'
  --exclude='config/my_const.php'
)

SSH_TARGET="${REMOTE_USER}@${REMOTE_HOST}"
SSH_BASE=(ssh -p "${REMOTE_PORT}" "${SSH_TARGET}")

echo "Local path:    ${LOCAL_PATH}"
echo "Remote target: ${SSH_TARGET}:${REMOTE_PATH}"

auto_backup() {
  local backup_name
  backup_name="acp_demo_backup_$(date +%F_%H%M).tgz"

  echo
  echo "==> Creating remote backup: ${backup_name}"
  "${SSH_BASE[@]}" "cd /home/convention/public_html && tar -czf '${backup_name}' acp_demo && ls -1 '/home/convention/public_html/${backup_name}'"
}

run_dry_run() {
  echo
  echo "==> Running rsync dry-run"
  rsync -azn --delete -e "ssh -p ${REMOTE_PORT}" --itemize-changes \
    "${RSYNC_EXCLUDES[@]}" \
    "${LOCAL_PATH}" "${SSH_TARGET}:${REMOTE_PATH}/"
}

run_deploy() {
  echo
  echo "==> Deploying with rsync"
  rsync -az --delete -e "ssh -p ${REMOTE_PORT}" \
    "${RSYNC_EXCLUDES[@]}" \
    "${LOCAL_PATH}" "${SSH_TARGET}:${REMOTE_PATH}/"
}

fix_ownership() {
  echo
  echo "==> Fixing ownership to ${REMOTE_OWNER}"
  "${SSH_BASE[@]}" "chown -R '${REMOTE_OWNER}' '${REMOTE_PATH}' && echo ownership-updated"
}

smoke_test() {
  echo
  echo "==> Smoke test: ${SMOKE_URL}"
  curl -I -L -s "${SMOKE_URL}" | head -n 5
}

if [[ "${RUN_BACKUP}" -eq 1 ]]; then
  auto_backup
else
  echo
  echo "==> Skipping backup by request"
fi

run_dry_run

if [[ "${DRY_RUN_ONLY}" -eq 1 ]]; then
  echo
  echo "Dry-run completed. No changes were deployed."
  exit 0
fi

if [[ "${AUTO_APPROVE}" -ne 1 ]]; then
  echo
  read -r -p "Proceed with LIVE deploy? Type 'yes' to continue: " confirm
  if [[ "${confirm}" != "yes" ]]; then
    echo "Deploy cancelled."
    exit 0
  fi
fi

run_deploy
fix_ownership
smoke_test

echo
echo "Deploy completed successfully."
