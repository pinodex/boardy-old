# Boardy
Simple PHP forum software. [Click here for Demo](http://boardy.pinodex.io/)

Demo email: admin@boardy.pinodex.io

Demo password: admin12345

### Requirements
- PHP 5.4 or higher
- MySQL
- Composer

### Installation
1. Clone this repository or download and extract the archive file.
2. Install dependancies with composer.
3. Visit http://example.com/install/ or http://example.com/install/index.php and proceed with the installation.

### Development status
This software is not in active development. I only work on this in my spare time. Therefore, not recommended for production use.

### Database Support
Boardy uses the Laravel Illuminate Database component so it supports MySQL, Postgres, SQLite, and SQL Server but the default installation configuration allows only MySQL to be used. I will add an option to use other databases later. But for now, you can use other database by manually editing the configuration file.

### URL Rewriting
##### Apache
URL Rewriting works well with Apache. An `.htaccess` file is already included here.
##### Nginx
It bugs on installation because `/install` and `/install/index.php` rewrites to `/index.php`.
