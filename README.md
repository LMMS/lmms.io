# lmms.io

This repository contains the source for LMMS's website, live at <https://lmms.io>.

<div align="center">

*lmms.io runs on DigitalOcean. Click the button below for more information, or access this link: https://m.do.co/c/c77894a32e56. Both will utilize our referral code.*

[![DigitalOcean Referral Badge](https://web-platforms.sfo2.cdn.digitaloceanspaces.com/WWW/Badge%201.svg)](https://www.digitalocean.com/?refcode=c77894a32e56&utm_campaign=Referral_Invite&utm_medium=Referral_Program&utm_source=badge)

</div>

## How to test the website locally

The website requires authentication with the GitHub API for fetching GitHub Discussions posts for the `/news` endpoint, with the authentication and data fetching itself being managed by the `knplabs/github-api` dependency. A [classic GitHub PAT (personal access token)](https://docs.github.com/en/authentication/keeping-your-account-and-data-secure/managing-your-personal-access-tokens#creating-a-personal-access-token-classic) is needed for local testing, which should be placed in the `.env.local` file on the root of the repository with the contents:

```ini
###> knplabs/github-api ###
GITHUB_AUTH_METHOD=client_id_header
GITHUB_USERNAME=...
GITHUB_SECRET=ghp_...
###< knplabs/github-api ###
```

The only required scope of this token is `read:discussion`, other scopes are unnecessary.

### Linux

1. Fork the repository [here](https://github.com/LMMS/lmms.io/fork)
2. Clone the forked repository.

```bash
git clone https://github.com/<your-username>/lmms.io.git
```

3. Get Composer

This project uses [Composer](http://getcomposer.org) for dependency management. You'll have to fetch those dependencies using Composer. For this, you must have Composer installed on your system. For quickly installing Composer locally on *nix, run:

Install PHP 8.2 and the required components.\
These commands are for Linux. It may be different from how it is installed on other OSes.

```bash
sudo add-apt-repository ppa:ondrej/php
sudo apt install curl php8.2 php8.2-xml php8.2-gd php8.2-intl php-symfony
```

```bash
cd lmms.io
curl -sS https://getcomposer.org/installer | php
```

> **Note:**
> You need to add `php.exe` to the Windows PATH, usually located in `c:\wamp\bin\php\phpx.y.z`
> For instructions for other OSes or for installing globally, visit Composer's [Getting Started](https://getcomposer.org/doc/00-intro.md) document.

1. Fetch dependencies using Composer.

After downloading Composer locally using the instructions above, fetch the dependencies by running the command below.

```bash
php composer.phar install
```

You'll have to run this command every time the dependencies in `composer.json` change.

### Windows

A convenient setup script is provided in `dev/windows/setup.ps1`. You just need to provide the path of where you've installed PHP, and it will setup PHP, install Composer, and install the project's dependencies on its own.

If you skipped automatic `.ini` validation or modification, there are some changes you'll need to make to your configuration file manually:

1. Locate `php.ini-development` or `php.ini` in the folder where you've installed/extracted the PHP release. This folder should also be where `php.exe` resides
   * If you have the `php.ini-development` file, remove the `-development` suffix from the file extension, the resulting file name should just be `php.ini`
   * If you would like to just edit your existing `php.ini`, leave it be.
2. Edit the file, and uncomment these lines:
   1. `;extension_dir = "ext"`
   2. `;extension=gd`
   3. `;extension=intl`
   4. `;extension=openssl`
   5. `;extension=pdo_mysql`
3. Save your edits, then re-run the setup script.

Not only does this allow the automatic script to execute, but also enable the local development server to function at all.

### macOS

> **Note**:
> For macOS, some dependencies must be [installed manually](https://superuser.com/a/1359317/443147).

1. Start the local server.

```bash
php -S localhost:8000 -t ./public/
```

You can then open <http://localhost:8000/> in a browser.

1. Optionally, configure the local `apache` and `nginx` instances.

With Apache:

```xml
 <Directory /home/user/lmms.io/public/>
  # add fallback resource to Apache config
  FallbackResource /index.php
 </Directory>
```

With Nginx:

```nginx
 # go to our front controller if none of them exists
 location / {
  try_files $uri $uri/ /index.php?$args;
 }
```
