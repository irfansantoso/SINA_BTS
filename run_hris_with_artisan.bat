@echo off
cd C:\xampp\htdocs\HRIS
php artisan schedule:run >> storage/logs/laravel-schedule.log 2>&1
