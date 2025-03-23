$phpDir = Read-Host "Enter the path of your PHP installation (e.g. 'C:\Program Files\php', 'D:\php', 'C:\wamp\bin\php')"
$fullPhpDir = Join-Path $phpDir "php.exe"

# check if path exists
if (-Not (Test-Path $phpDir)) {
  throw "The directory '$phpDir' does not exist. Did you point to the exact file instead of the containing folder?"
}

# check if php.exe is there
if (-Not (Test-Path $fullPhpDir)) {
  Write-Error "'php.exe' was not found in '$phpDir'. Was it installed correctly?"
  exit 1
}

# shift to root dir
if ($pwd -match "(\\dev\\windows)") {
  Set-Location "../../"
}

# download composer
$composerInstallerUrl = "https://getcomposer.org/installer"
$composerInstallerPath = Join-Path $pwd "composer-setup.php"
Invoke-WebRequest -Uri $composerInstallerUrl -OutFile $composerInstallerPath

# check if composer's installer exists
if (-Not (Test-Path $composerInstallerPath)) {
  Write-Error "Failed to download Composer installer"
  exit 1
}

Start-Process -FilePath $fullPhpDir -ArgumentList $composerInstallerPath -Wait -NoNewWindow

Start-Process -FilePath $fullPhpDir -ArgumentList "composer.phar install" -Wait -NoNewWindow

# cleanup composer's installer
Remove-Item $composerInstallerPath
