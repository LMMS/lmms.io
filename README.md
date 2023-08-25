# lmms.io

This repository contains the source for LMMS's website, live at <https://lmms.io>.

## How to test the website locally

1. Fork the repository [here](https://github.com/LMMS/lmms.io/fork)
2. Clone the forked repository.

```bash
git clone https://github.com/<your-username>/lmms.io.git
```

3. Get Composer

This project uses [Composer](http://getcomposer.org) for dependency management. You'll have to fetch those dependencies using Composer. For this, you must have Composer installed on your system. For quickly installing Composer locally on *nix, run:
	
If not already, install PHP and the required components.\
These commands are for Linux. It may be different from how it is installed on other OSes.

```bash
sudo apt install curl php php-xml php-gd php-intl php-symfony
```
	
```bash
cd lmms.io
curl -sS https://getcomposer.org/installer | php
```

For installing Composer locally on Windows (i.e. Wamp), run:

```bash
cd lmms.io
php -r "eval('?>'.file_get_contents('https://getcomposer.org/installer'));"
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
