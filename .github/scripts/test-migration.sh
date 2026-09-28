#!/bin/bash
#
# マイグレーションとインストール用 SQL の整合性を検証する
#
# 1. 新規インストールした環境で、全マイグレーションが適用済みとして登録されていること
# 2. 基準バージョンの SQL でインストールしてからマイグレーションを実行した環境と、
#    新規インストールした環境のスキーマが一致すること
#
# docker compose で EC-CUBE を起動した状態で実行する
#
# Usage: .github/scripts/test-migration.sh <mysql|pgsql|sqlite3>

set -euo pipefail

DB=${1:?"Usage: $0 <mysql|pgsql|sqlite3>"}
BASE_TAG=${BASE_TAG:-eccube-2.25.0}
TBLS_IMAGE=${TBLS_IMAGE:-ghcr.io/k1low/tbls:v1.96.0@sha256:35e29e5c2e2d8a4555b36eacfdac9d78d8af5aee5c73cc68219cac9cf3723ee1}
UPGRADE_DBNAME=eccube_upgrade
OUT_DIR=${OUT_DIR:-$(mktemp -d)}

exec_ec_cube() {
    docker compose exec -T ec-cube "$@"
}

echo "==> 新規インストールでマイグレーションが適用済みとして登録されていることを確認"
exec_ec_cube php data/vendor/bin/eccube migrate:status | tee "${OUT_DIR}/status.txt"
if ! grep -q ', 0 pending)' "${OUT_DIR}/status.txt"; then
    echo "ERROR: 未適用のマイグレーションがあります" >&2
    exit 1
fi

if [ "${DB}" = "sqlite3" ]; then
    # SQLite3 は基準バージョンで未サポートのため、アップグレードの検証は行わない
    exit 0
fi

DB_USER=$(exec_ec_cube printenv DB_USER)
DB_PASSWORD=$(exec_ec_cube printenv DB_PASSWORD)
DB_SERVER=$(exec_ec_cube printenv DB_SERVER)
DB_NAME=$(exec_ec_cube printenv DB_NAME)
case "${DB}" in
    pgsql )
        DB_PORT=5432
        INSTALL_USER=${DB_USER}
        INSTALL_PASSWORD=${DB_PASSWORD}
        dsn() { echo "postgres://${INSTALL_USER}:${INSTALL_PASSWORD}@${DB_SERVER}:${DB_PORT}/$1?sslmode=disable"; }
        ;;
    mysql )
        DB_PORT=3306
        # eccube_install.sh は root でデータベースを作成するため、基準バージョンは root でインストールする
        INSTALL_USER=root
        INSTALL_PASSWORD=${DB_PASSWORD}
        dsn() { echo "mysql://${INSTALL_USER}:${INSTALL_PASSWORD}@${DB_SERVER}:${DB_PORT}/$1"; }
        ;;
    * )
        echo "ERROR: unsupported database: ${DB}" >&2
        exit 1
        ;;
esac

echo "==> ${BASE_TAG} でインストール"
git archive "${BASE_TAG}" | exec_ec_cube sh -c 'rm -rf /tmp/base && mkdir -p /tmp/base && tar -x -C /tmp/base'
docker compose exec -T -w /tmp/base \
    -e DBUSER="${INSTALL_USER}" -e DBPASS="${INSTALL_PASSWORD}" -e ROOTPASS="${INSTALL_PASSWORD}" \
    -e DBNAME="${UPGRADE_DBNAME}" -e DBPORT="${DB_PORT}" -e DBSERVER="${DB_SERVER}" \
    ec-cube ./eccube_install.sh "${DB}" > "${OUT_DIR}/install-base.log"

echo "==> マイグレーションを実行"
# config.php は defined() or define() 形式のため、先に定義して接続先を切り替える
exec_ec_cube sh -c "cat > /tmp/upgrade-db.php" <<__EOF__
<?php
define('DB_NAME', '${UPGRADE_DBNAME}');
define('DB_USER', '${INSTALL_USER}');
define('DB_PASSWORD', '${INSTALL_PASSWORD}');
__EOF__
exec_ec_cube php -d auto_prepend_file=/tmp/upgrade-db.php data/vendor/bin/eccube migrate

echo "==> スキーマを比較"
NETWORK=$(docker inspect -f '{{range $k, $v := .NetworkSettings.Networks}}{{$k}}{{"\n"}}{{end}}' "$(docker compose ps -q ec-cube)" | grep -m1 'backend')
# テーブル定義 (def) とコメントは比較対象外。インデックスと制約は名前順に並べる
NORMALIZE='del(.name)
    | .tables |= (map(del(.def, .comment)
        | .columns |= map(del(.comment))
        | .indexes |= ((. // []) | sort_by(.name))
        | .constraints |= ((. // []) | sort_by(.name))
        | .triggers |= (. // []))
    | sort_by(.name))'
for target in "${DB_NAME}:fresh" "${UPGRADE_DBNAME}:upgrade"; do
    docker run --rm --network "${NETWORK}" "${TBLS_IMAGE}" out -t json "$(dsn "${target%%:*}")" |
        jq -S "${NORMALIZE}" > "${OUT_DIR}/${target##*:}.json"
done
if ! diff -u "${OUT_DIR}/fresh.json" "${OUT_DIR}/upgrade.json"; then
    echo "ERROR: 新規インストールとアップグレードでスキーマが一致しません" >&2
    echo "マイグレーションとインストール用 SQL の両方が更新されているか確認してください (CONTRIBUTING.md)" >&2
    exit 1
fi
echo "OK: スキーマが一致しました"

if [ "${DB}" = "pgsql" ]; then
    echo "==> テーブル定義書との差分を確認"
    docker compose exec -T postgres psql -q -v ON_ERROR_STOP=1 -U "${DB_USER}" "${DB_NAME}" < html/install/sql/comment_set_pgsql.sql
    if ! docker run --rm --network "${NETWORK}" -v "${PWD}:/work" -e TBLS_DSN="$(dsn "${DB_NAME}")" "${TBLS_IMAGE}" diff -c /work/docs/.tbls.yml; then
        echo "ERROR: テーブル定義書 (docs/database-schema) が最新ではありません" >&2
        echo "docs/README.md の手順でテーブル定義書を更新してください" >&2
        exit 1
    fi
    echo "OK: テーブル定義書が最新です"
fi
