#!/bin/sh
set -e

# 1. フラグの状態を管理する変数（デフォルトは false）
INITIALIZE=false

# 2. 引数の解析（while + case + shift 方式）
while [ $# -gt 0 ]; do
  case "$1" in
    --initialize)
      INITIALIZE=true
      shift
      ;;
    -h|--help)
      echo "使い方: $0"
      exit 0
      ;;
    *)
      # 未知の引数は無視するか、エラーにする
      shift
      ;;
  esac
done

# 3. セーフティチェック
if [ "$INITIALIZE" = false ]; then
  echo "--------------------------------------------------------"
  echo "警告: この操作はシステムを初期化します。"
  echo "実行するには '--initialize' フラグを明示的に指定してください。"
  echo "例: $0 --initialize"
  echo "--------------------------------------------------------"
  exit 1
fi

cd "$(dirname "$0")/.."

mkdir -p /mnt/efs/laravel/database

php artisan migrate
php artisan db:seed
