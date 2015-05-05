# Boardy
Simple PHP forum software. [Click here for Demo](http://boardy.pinodex.io/)

Demo email: admin@boardy.pinodex.io

Demo password: admin12345

### Requirements
- PHP 5.4 or higher
- MySQL or PostgreSQL
- Composer

### Installation
1. Clone this repository or download and extract the archive file.
2. Install dependancies with composer.
3. Visit http://example.com/install/ or http://example.com/install/index.php and proceed with the installation.

### Development status
This software is not in active development. I only work on this in my spare time. Therefore, not recommended for production use.

### URL Rewriting
##### Apache
URL Rewriting works well with Apache. An `.htaccess` file is already included here.
##### Nginx
It bugs on installation because `/install` and `/install/index.php` rewrites to `/index.php`.

### Plans
https://github.com/pinodex/boardy/issues/2