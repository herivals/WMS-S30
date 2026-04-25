Restauration bdd
docker exec -i wms-s30-database-1 pg_restore -U postgres -d wms --clean --if-exists --no-owner --no-privileges < wms_20260422152849.dump

php bin/console app:charges:import-csv --env=prod
Import charge

Users
docker exec -i wms-s30-app-1 pg_restore -U postgres -d wms --clean --if-exists --no-owner --no-privileges < wms_20260422.dump
