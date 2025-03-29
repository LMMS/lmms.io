param (
  [ValidateScript({ Test-Path $_ })]
  [string] $phpDir
)

# check if php exists in Path, then set it as full PHP dir
if (-not [string]::IsNullOrEmpty((Get-Command php).Source)) {
  $phpDir = Split-Path (Get-Command php).Source
  $fullPhpDir = (Get-Command php).Source
}
# if not, then check if the phpDir arg exists
elseif ([string]::IsNullOrEmpty($phpDir)) {
  $phpDir = Read-Host "Enter the path of your PHP installation (e.g. 'C:\Program Files\php', 'D:\php', 'C:\wamp\bin\php')"

  # check if given path exists
  if (-not (Test-Path $phpDir)) {
    throw "The directory '$phpDir' does not exist. Did you point to the exact file instead of the containing folder?"
  }

  $fullPhpDir = Join-Path $phpDir "php.exe"

  # check if php.exe is there
  if (-not (Test-Path $fullPhpDir)) {
    throw "'php.exe' was not found in '$phpDir'. Was it installed correctly?"
  }
}

Write-Host -ForegroundColor Gray "[setup] Using $fullPhpDir as PHP runner"

function Validate-Ini {
  Write-Host "[setup] Validating .ini file"

  # ungodly regexes of setting lines that are commented
  # bulletproof except for the most egregious
  $iniSettingsRegex = @(
    ";\s*\s*extension_dir\s*=\s*(`"ext`")"
    ";\s*\s*extension\s*=\s*(gd)\b"
    ";\s*\s*extension\s*=\s*(intl)\b"
    ";\s*\s*extension\s*=\s*(openssl)\b"
    ";\s*\s*extension\s*=\s*(pdo_mysql)\b"
    ";\s*\s*extension\s*=\s*(zip)\b"
  )

  # will change to false if the validation fails
  $iniValid = $true

  $iniFilePath = Join-Path $phpDir "php.ini"
  $iniFileDevPath = Join-Path $phpDir "php.ini-development"

  # if the .ini doesn't exist, but the dev template does
  if (-not (Test-Path $iniFilePath) -and (Test-Path $iniFileDevPath)) {
    $confirm = Read-Host "You do not have a 'php.ini' file, but you have the 'php.ini-development' file. Would you like the script to enable the development .ini? Saying [n] will exit the script [y/n]"

    if ($confirm -match "y") {
      Write-Host "[setup] Renaming 'php.ini-development' to 'php.ini'"
      Rename-Item -Path $iniFileDevPath -NewName "php.ini"
    }
    else {
      throw "Exiting since the script must use 'php.ini'. You must rename/create the file yourself"
    }
  }
  # if none was found, then we're inbetween a rock and a hard place
  elseif (-not (Test-Path $iniFilePath) -and -not (Test-Path $iniFileDevPath)) {
    throw "'php.ini' and 'php.ini-development' cannot be found. Was PHP installed correctly?"
  }

  $iniFile = Get-Content $iniFilePath

  # actual file validation starts here
  foreach ($line in $iniFile) {
    foreach ($settingRegex in $iniSettingsRegex) {
      if ($line -match $settingRegex) {
        $iniValid = $false
      }
    }
  }

  # .ini is invalid, prompt to fix
  if ($iniValid -eq $false) {
    $confirm = Read-Host "[setup] Your .ini file is not suitable for running the local test environment. Would you like the script to modify the file and enable the relevant settings? The script will only modify the settings needed for the project to run and leave others unchanged, you can view the list of settings that needs to be enabled in the root README [y/n]"

    if ($confirm -match "y") {
      Write-Host "[setup] Modifying and writing settings"

      # read-write each line, and uncomment lines that match the regex
      $iniFileNew = ""
      foreach ($iniLine in $iniFile) {
        $isSettingLine = $false
        foreach ($settingRegex in $iniSettingsRegex) {
          if ($iniLine -match $settingRegex) {
            $isSettingLine = $true
            $iniFileNew += $iniLine.Replace(";", "") + "`n"
            break
          }
        }
        if (-not $isSettingLine) {
          $iniFileNew += $iniLine + "`n"
        }
      }

      Write-Host -ForegroundColor Green "[setup] Settings file modified"

      # the modified file hasn't been written yet, write it now, per-line
      Clear-Content -Path $iniFilePath
      foreach ($line in $iniFileNew) {
        Add-Content -Path $iniFilePath -Value $line
      }

      Write-Host -ForegroundColor Green "[setup] Succesfully validated and written '.ini' settings, continuing setup"
    }
    # take a chance that the install process will continue to run fine, probably not
    else {
      Write-Host -ForegroundColor Yellow "[setup] Skipping .ini file modification. You must enable the relevant settings by yourself. Refer to the README for instructions on how to enable the settings manually."
      Write-Host -ForegroundColor Red "[setup] There's a chance that the script will error after this"
    }
  }
  #
  else {
    Write-Host -ForegroundColor Green "[setup] All settings are valid, continuing setup"
  }
}

Validate-Ini


function Setup-Composer {
  # shift to root dir
  if ($pwd -match "(\\dev\\windows)") {
    Set-Location "../../"
  }

  # download composer
  Write-Host "[setup] Dowloading composer's installer"
  $composerInstallerUrl = "https://getcomposer.org/installer"
  $composerInstallerPath = Join-Path $pwd "composer-setup.php"
  Invoke-WebRequest -Uri $composerInstallerUrl -OutFile $composerInstallerPath

  # check if composer's installer exists
  if (-Not (Test-Path $composerInstallerPath)) {
    throw "Failed to download Composer installer"
  }

  Write-Host "[setup] Installing composer"
  Start-Process -FilePath $fullPhpDir -ArgumentList $composerInstallerPath -Wait -NoNewWindow

  Write-Host "[setup] Getting dependencies"
  Start-Process -FilePath $fullPhpDir -ArgumentList "composer.phar install" -Wait -NoNewWindow

  # cleanup composer's installer
  Write-Host "[setup] Cleaning up composer's installer"
  Remove-Item $composerInstallerPath
}

Setup-Composer

Write-Host -ForegroundColor Green "[setup] Setup complete! Run 'php -S localhost:8000 -t ./public/' to start the local dev server"

