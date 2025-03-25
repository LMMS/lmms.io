param (
  [Alias("PSPath")]
  [string] $phpDir
)

# check if the script param is empty
if ([string]::IsNullOrEmpty($phpDir)) {
  $phpDir = Read-Host "Enter the path of your PHP installation (e.g. 'C:\Program Files\php', 'D:\php', 'C:\wamp\bin\php')"
}

$fullPhpDir = Join-Path $phpDir "php.exe"

# check if path exists
if (-Not (Test-Path $phpDir)) {
  throw "The directory '$phpDir' does not exist. Did you point to the exact file instead of the containing folder?"
}

# check if php.exe is there
if (-Not (Test-Path $fullPhpDir)) {
  throw "'php.exe' was not found in '$phpDir'. Was it installed correctly?"
}

Write-Host "Using $fullPhpDir as PHP runner."


function Validate-Ini {
  $confirm = Read-Host "Would you like for the script to validate your PHP`'s .ini file? [y/n]"

  if ($confirm -match "n") {
    Write-Host -ForegroundColor Yellow "Skipping .ini file validation. If the setup script fails further down the line, you can re-run the setup script and say 'y' on .ini validation, or refer to the README on how to enable the particular settings needed"
    return
  }
  else {
    Write-Host "Validating .ini file"
  }

  $iniSettings = @(
    ";extension_dir = `"ext`""
    ";extension=gd"
    ";extension=intl"
    ";extension=openssl"
    ";extension=pdo_mysql"
  )
  $iniValid = $true

  $iniFilePath = Join-Path $phpDir "php.ini"
  $iniFileDevPath = Join-Path $phpDir "php.ini-development"
  if (-not (Test-Path $iniFilePath) -and (Test-Path $iniFileDevPath)) {
    $confirm = Read-Host "You do not have a 'php.ini' file, but you have the 'php.ini-development' file. Would you like the script to enable the development .ini? Saying [n] will exit the script [y/n]"

    if ($confirm -match "y") {
      Write-Host "Renaming 'php.ini-development' to 'php.ini'"
      Rename-Item -Path $iniFileDevPath -NewName "php.ini"
    }
    else {
      throw "Exiting since the script must use 'php.ini'. You must rename/create the file yourself"
    }
  }
  elseif (-not (Test-Path $iniFilePath) -and -not (Test-Path $iniFileDevPath)) {
    throw "'php.ini' and 'php.ini-development' cannot be found. Was PHP installed correctly?"
  }

  $iniFile = Get-Content $iniFilePath

  foreach ($line in $iniFile) {
    foreach ($setting in $iniSettings) {
      if ($line -match $setting) {
        $iniValid = $false
      }
    }
  }

  if ($iniValid -eq $false) {
    $confirm = Read-Host "Your .ini file is not suitable for running the local test environment. Would you like the script to modify the file and enable the relevant settings? The script will only modify the settings needed for the project to run and leave others unchanged, you can view the list of settings that needs to be enabled in the root README [y/n]"

    if ($confirm -match "y") {
      Write-Host "Modifying and writing settings"

      $iniFileNew = ""
      foreach ($iniLine in $iniFile) {
        $isSettingLine = $false
        foreach ($setting in $iniSettings) {
          if ($iniLine -match $setting) {
            $isSettingLine = $true
            $iniFileNew += $iniLine.Replace(";", "") + "`n"
            break
          }
        }
        if (-not $isSettingLine) {
          $iniFileNew += $iniLine + "`n"
        }
      }

      Write-Host -ForegroundColor Green "Settings file modified"

      Clear-Content -Path $iniFilePath
      foreach ($line in $iniFileNew) {
        Add-Content -Path $iniFilePath -Value $line
      }

      Write-Host -ForegroundColor Green "Your .ini settings are valid, continuing script"
    }
    else {
      Write-Host -ForegroundColor Yellow "Skipping .ini file modification. You must enable the relevant settings by yourself. Refer to the README for instructions on how to enable the settings manually."
      Write-Host -ForegroundColor Red "There's a chance that the script will error after this"
    }
  }
  else {
    Write-Host -ForegroundColor Green "All settings are valid, continuing setup"
  }
}

Validate-Ini

# shift to root dir
if ($pwd -match "(\\dev\\windows)") {
  Set-Location "../../"
}

# download composer
Write-Host "Dowloading composer's installer"
$composerInstallerUrl = "https://getcomposer.org/installer"
$composerInstallerPath = Join-Path $pwd "composer-setup.php"
Invoke-WebRequest -Uri $composerInstallerUrl -OutFile $composerInstallerPath

# check if composer's installer exists
if (-Not (Test-Path $composerInstallerPath)) {
  throw "Failed to download Composer installer"
}

Write-Host "Installing composer"
Start-Process -FilePath $fullPhpDir -ArgumentList $composerInstallerPath -Wait -NoNewWindow

Write-Host "Getting dependencies"
Start-Process -FilePath $fullPhpDir -ArgumentList "composer.phar install" -Wait -NoNewWindow

# cleanup composer's installer
Write-Host "Cleaning up composer's installer"
Remove-Item $composerInstallerPath

Write-Host -ForegroundColor Green "Setup complete! Run 'php -S localhost:8000 -t ./public/' to start the local dev server"