Add-Type -AssemblyName System.Windows.Forms
Add-Type -AssemblyName System.Drawing

$botDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$repo = Split-Path -Parent $botDir
$logFile = Join-Path $botDir 'bot.log'
$errLogFile = Join-Path $botDir 'bot.err.log'
$verboseFile = Join-Path $repo 'verbose.on'

$script:botProcess = $null

function Start-Bot {
    if ($script:botProcess -and -not $script:botProcess.HasExited) { return }

    $node = (Get-Command node.exe -ErrorAction SilentlyContinue | Select-Object -First 1).Source
    if (-not $node) { $node = 'C:\Program Files\nodejs\node.exe' }

    $script:botProcess = Start-Process -FilePath $node -ArgumentList 'bot.js' `
        -WorkingDirectory $botDir -WindowStyle Hidden `
        -RedirectStandardOutput $logFile -RedirectStandardError $errLogFile -PassThru
}

function Stop-Bot {
    if ($script:botProcess -and -not $script:botProcess.HasExited) {
        $script:botProcess.Kill()
        $script:botProcess.WaitForExit(5000) | Out-Null
    }
    $script:botProcess = $null
}

$notify = New-Object System.Windows.Forms.NotifyIcon
$notify.Icon = [System.Drawing.SystemIcons]::Application
$notify.Text = 'OCI ARM Bot'
$notify.Visible = $true

$menu = New-Object System.Windows.Forms.ContextMenuStrip
$statusItem = $menu.Items.Add('Status')
$verboseItem = $menu.Items.Add('Toggle verbose')
$logItem = $menu.Items.Add('Show log')
$restartItem = $menu.Items.Add('Restart bot')
$menu.Items.Add('-') | Out-Null
$exitItem = $menu.Items.Add('Exit')

$statusItem.Add_Click({
    $running = if ($script:botProcess -and -not $script:botProcess.HasExited) { 'running' } else { 'stopped' }
    $verbose = if (Test-Path $verboseFile) { 'ON' } else { 'OFF' }
    $notify.ShowBalloonTip(4000, 'OCI ARM Bot', "bot: $running`nverbose: $verbose", [System.Windows.Forms.ToolTipIcon]::Info)
})

$verboseItem.Add_Click({
    if (Test-Path $verboseFile) { Remove-Item $verboseFile -Force } else { Set-Content -Path $verboseFile -Value '1' }
})

$logItem.Add_Click({
    if (Test-Path $logFile) { Start-Process notepad.exe $logFile } else { [System.Windows.Forms.MessageBox]::Show('No log yet.') }
})

$restartItem.Add_Click({ Stop-Bot; Start-Sleep -Seconds 1; Start-Bot })

$exitItem.Add_Click({
    Stop-Bot
    $notify.Visible = $false
    $notify.Dispose()
    [System.Windows.Forms.Application]::Exit()
})

$notify.DoubleClick = { $statusItem.PerformClick() }

$timer = New-Object System.Windows.Forms.Timer
$timer.Interval = 15000
$timer.Add_Tick({ if (-not $script:botProcess -or $script:botProcess.HasExited) { Start-Bot } })
$timer.Start()

Start-Bot
[System.Windows.Forms.Application]::Run()
