lmms.io
======

This repo contains the source for LMMS's website located at https://lmms.io.

## How to test the website locally ##

1. Clone the code

	```bash
	$ git clone https://github.com/LMMS/lmms.io.git
	```

1. Get Composer

	This project uses [Composer](http://getcomposer.org) for dependency management. You'll have to fetch those dependencies using composer. For this, you must have Composer installed on your system. For quickly installing Composer locally on *nix, run:
	
	```bash
	$ cd lmms.io
	$ curl -sS https://getcomposer.org/installer | php
	```
	
	For installing Composer locally on Windows (i.e. Wamp), run:
	```bash
	> cd lmms.io
	> php -r "eval('?>'.file_get_contents('https://getcomposer.org/installer'));"
	```
	
	> **Note:** For this to work on Windows you need the php.exe processor to be on your path, usually located in `c:\wamp\bin\php\phpx.y.z`
	
	For instructions for other OSs or for installing globally, visit Composer's [Getting Started](https://getcomposer.org/doc/00-intro.md) document.
   
1. Fetch dependencies using composer

	When you downloaded composer locally using the instructions above, fetch the dependencies by running
   
	```bash
	$ php composer.phar install
	```
   
	You'll have to run this comand every time the dependencies in `composer.json` change.
1. Configure local server
	
	Apache:
	```xml
	<Directory /home/user/lmms.io/public/>
		# add fallback resource to Apache config
		FallbackResource /index.php
	</Directory>
	```
	
	Nginx:
	```nginx
	# go to our front controller if none of them exists
	location / {
		try_files $uri $uri/ /index.php?$args;
	}
	```
	
1. Start local server

	```bash
	$ php -S localhost:8000 -t ./public/
	```
	
	You can then open http://localhost:8000/ in a browser.
