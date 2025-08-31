Set WshShell = CreateObject("WScript.Shell")

' Run batch files silently
WshShell.Run Chr(34) & "C:\xampp\htdocs\bulk-mailer-II\loader\app-loader-serve.bat" & Chr(34), 0
WshShell.Run Chr(34) & "C:\xampp\htdocs\bulk-mailer-II\loader\app-loader-npm.bat" & Chr(34), 0
WshShell.Run Chr(34) & "C:\xampp\htdocs\bulk-mailer-II\loader\app-loader-reverb.bat" & Chr(34), 0
WshShell.Run Chr(34) & "C:\xampp\htdocs\bulk-mailer-II\loader\app-loader-queue.bat" & Chr(34), 0
WshShell.Run Chr(34) & "C:\xampp\htdocs\bulk-mailer-II\loader\app-loader-schedule.bat" & Chr(34), 0