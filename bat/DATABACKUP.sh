# BACKUP DATABASE
PGPASSWORD='UedaUskk' pg_dump -U ueda uedaorder > /ADD_DISK/DB/Backup/$(date +%Y%m%d)_uedaDB.sql

# Delete backups older than 7 days
find /ADD_DISK/DB/Backup -name "*_uedaDB.sql" -mtime +6 -exec rm {} \;
