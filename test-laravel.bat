@echo off
cd /d "c:\wamp64\www\test\12\app-variations"
echo Testing Laravel connection...
php artisan migrate:status
pause
