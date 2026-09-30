Set fso = CreateObject("Scripting.FileSystemObject")
scriptDir = fso.GetParentFolderName(WScript.ScriptFullName)
repo = fso.GetParentFolderName(scriptDir)
Set sh = CreateObject("WScript.Shell")
sh.CurrentDirectory = repo

If WScript.Arguments.Count = 0 Then WScript.Quit
target = WScript.Arguments(0)

sh.Run "powershell.exe -NoProfile -ExecutionPolicy Bypass -WindowStyle Hidden -File """ & target & """", 0, False
