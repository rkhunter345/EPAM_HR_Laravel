@echo off
cd /d "%~dp0"
where composer >nul 2>nul
if errorlevel 1 (
  echo [EPAM HR] Composer no esta disponible. Consulta requisitos.txt
  pause
  exit /b 1
)
if not exist .env copy .env.example .env >nul
php artisan key:generate
php artisan migrate:fresh --seed
echo.
echo   Abre http://127.0.0.1:8000  (admin / epam123)
echo.
php artisan serve
pause
