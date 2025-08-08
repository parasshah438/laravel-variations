@echo off
cd /d "c:\wamp64\www\test\12\app-variations"
echo Rolling back and recreating user_behaviors table...
php artisan migrate:rollback --step=1
php artisan migrate
echo Done!
pause
