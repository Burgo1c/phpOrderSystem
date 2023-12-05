# BACKUP DATABASE
PGPASSWORD='password' pg_dump -U user public > /ADD_DISK/DB/Backup/$(date +%Y%m%d)_DB.sql

# Delete backups older than 7 days
find /ADD_DISK/DB/Backup -name "*_DB.sql" -mtime +6 -exec rm {} \;
