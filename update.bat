@echo off
:loop
cls

echo Make a new release:

:: Prompt the user for a commit message
set /p commitMessage=commit: 

:: Check if the user wants to exit
if /I "%commitMessage%"=="exit" goto end

:: Declare a variable for the commit message
set message=%commitMessage%

echo.
echo.
git add .

:: Commit with the variable
git commit -m "%message%"

echo.
:: Push to the repository
git push origin main

echo.
echo =====================================
echo.
echo Commit and push completed.
echo.
pause
goto loop

:end
echo Exiting...
