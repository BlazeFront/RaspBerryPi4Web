@echo off
:: Stage all changes
git add .

:: Prompt the user for a commit message
set /p commitMessage=Enter commit message: 

:: Commit with the provided message
git commit -m "%commitMessage%"

:: Push to the repository
git push origin main

pause
