#!/usr/bin/env bash
# Levanta la base (Docker) y el sistema (servidor PHP).
#   ./levantar.sh              -> inicia todo con la base en estado inicial (la recrea)
#   ./levantar.sh --conservar  -> inicia todo conservando los datos anteriores
#   Puerto web: PORT=9000 ./levantar.sh   (por defecto 8000)
set -euo pipefail
cd "$(dirname "$0")"

PORT="${PORT:-8000}"

command -v docker >/dev/null || { echo "Falta Docker."; exit 1; }
command -v php    >/dev/null || { echo "Falta PHP (con pdo_pgsql)."; exit 1; }
docker info >/dev/null 2>&1   || { echo "Docker no está corriendo. Abrí Docker Desktop."; exit 1; }

# Cada vez que se inicia, la base se borra y se recrea desde db/init.sql
# (estado inicial limpio). Para conservar los datos: ./levantar.sh --conservar
if [ "${1:-}" != "--conservar" ]; then
  echo "→ Reiniciando la base al estado inicial..."
  if docker container inspect gym-db >/dev/null 2>&1; then
    VOLS=$(docker inspect -f '{{range .Mounts}}{{if .Name}}{{.Name}} {{end}}{{end}}' gym-db)
    docker rm -f gym-db >/dev/null
    [ -n "$VOLS" ] && docker volume rm $VOLS >/dev/null 2>&1 || true
  fi
  docker compose down -v >/dev/null 2>&1 || true
fi

echo "→ Levantando PostgreSQL en Docker..."
docker compose up -d --wait db

export DB_HOST=127.0.0.1 DB_PORT=5433 DB_NAME=BaseDatosGym DB_USER=gym DB_PASS=gym123

echo
echo "✔ Sistema en http://localhost:$PORT   (Ctrl+C para detener el servidor)"
echo "  admin   : admin@gym.com   / admin123"
echo "  cliente : cliente@gym.com / admin123"
echo "  CU1 → /servicios      CU2 → /pagos"
echo
exec php -S "localhost:$PORT" -t "gym mvc" "gym mvc/router.php"
