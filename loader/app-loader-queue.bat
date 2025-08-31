@echo off
cd /d "C:\xampp\htdocs\bulk-mailer-II"
php artisan queue:work --tries=3