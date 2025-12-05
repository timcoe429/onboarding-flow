@echo off
echo Starting WordPress Docker containers...
docker compose down
docker compose up -d
echo Done.
pause
