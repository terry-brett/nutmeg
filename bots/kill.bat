taskkill /f /im timeout.exe

set /p prob=< val
if %prob% NEQ  1.0 start cmd /k .\run_ai.bat
