set list=1 3 4 5 6 7 8 9 10 11 12 13 14 15 16 17 18 19 20 21 22 23 24 25 26 27 28 29 30

::start cmd /k python .\reset_db.py
::timeout /t 2
::start cmd /k python .\infected.py
::timeout /t 1 
FOR %%A IN (%list%) DO (
  start cmd /k python .\main.py user%%A 
)
timeout /t 90 
taskkill /f /im python.exe
taskkill /f /im cmd.exe
