@echo off
title Simulador de Vendas NEF

:loop
echo Executando simulate:sale em %date% %time%
php artisan simulate:sale --random --occurrence=venda

REM Aguarda 10 segundos
timeout /t 10 /nobreak > nul

goto loop