#!/usr/bin/env bash
# Apaga y elimina la base (contenedor y datos). La próxima vez arranca limpia.
cd "$(dirname "$0")"
docker rm -f gym-db >/dev/null 2>&1 || true
docker compose down -v
