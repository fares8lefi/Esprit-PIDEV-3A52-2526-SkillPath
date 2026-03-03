#!/usr/bin/env bash
# scripts/ci.sh — Tests locaux (PHPStan + PHPUnit)
# Usage : bash scripts/ci.sh

set -e

echo ""
echo "════════════════════════════════════════"
echo "  1/2  PHPStan — Analyse statique"
echo "════════════════════════════════════════"
vendor/bin/phpstan analyse --memory-limit=512M

echo ""
echo "════════════════════════════════════════"
echo "  2/2  PHPUnit — Tests"
echo "════════════════════════════════════════"
php bin/phpunit --testdox

echo ""
echo " Tous les tests passent !"
echo ""
echo "Pour lancer Docker :"
echo "  docker compose up -d --build"
echo "  Puis ouvrir http://localhost:8080"
