cd C:\lib
@ECHO off
cls
:start
ECHO.
ECHO 1. server 1
ECHO 2. server 2
set choice=
set /p choice=Which server?
if not '%choice%'=='' set choice=%choice:~0,1%
if '%choice%'=='1' goto 1
if '%choice%'=='2' goto 2
ECHO "%choice%" is not valid, try again
ECHO.
goto start
:1
ideviceactivation activate -s sliverby.000webhostapp.com/sliver.php -d
@ECHO OFF

:choice
set /P c=Did it work?[Y/N]?
if /I "%c%" EQU "Y" goto :end
if /I "%c%" EQU "N" goto :ots1

:ots1
set /P c=Try other server?[Y/N]?
if /I "%c%" EQU "Y" goto :2
if /I "%c%" EQU "N" goto :end

goto end
:2
ideviceactivation activate -s cengdealajr.gearhostpreview.com/sliver.php -d
@ECHO OFF

:choice
set /P c=Did it work?[Y/N]?
if /I "%c%" EQU "Y" goto :end
if /I "%c%" EQU "N" goto :ots2

:ots2
set /P c=Try other server?[Y/N]?
if /I "%c%" EQU "Y" goto :1
if /I "%c%" EQU "N" goto :end

goto end
:end
pause