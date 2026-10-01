$ErrorActionPreference = 'SilentlyContinue'

$count = 0

$trays = Get-CimInstance Win32_Process -Filter "Name='powershell.exe'" | Where-Object { $_.CommandLine -like '*tray.ps1*' }
foreach ($t in $trays) {
    Stop-Process -Id $t.ProcessId -Force
    $count++
}

$nodes = Get-Process node
foreach ($n in $nodes) {
    Stop-Process -Id $n.Id -Force
    $count++
}

foreach ($task in 'OCI ARM Capacity', 'OCI ARM Summary') {
    Disable-ScheduledTask -TaskName $task -ErrorAction SilentlyContinue | Out-Null
}

Write-Host "Stopped $count process(es) and disabled scheduled tasks."
