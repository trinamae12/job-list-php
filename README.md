## Project Overview
This project was created from [udemy tutorial by Traversy](https://www.udemy.com/share/10a4Ly3@r7mGfyHjPdNVqBnIqHUMSt6q9NQXcDraCCA43CkN7MLzNVVBTloQ0GazIkd2JtlZow==/) 
but added with Docker setup. There may be customizations and changes for this project along the way because this serves as my learning ground for native PHP.

## Setup Instructions
1. Clone repository
2. Open terminal and go to root project directory
3. Create folder named `config` and add a file under it named `db.php`.
4. Write this code inside `config/db.php`
   ```
   <?php
   return [
     'host' => '{your_host_name}',
    'port' => '{port}',
    'dbName' => '{database_name}',
    'username' => '{username}',
    'password' => '{password}',
   ];
   ```
6. Open `docker-compose.yml`, change credentials for `MYSQL_DATABASE`, `MYSQL_USER`, `MYSQL_PASSWORD`, `MYSQL_ROOT_PASSWORD`
7. You can edit `docker-compose.yml` and `Dockerfile to suit your needs`
8. Run command `docker-compose up -d --build`
9. Open container named `www-1`
10. Run `composer install`
11. Open `localhost:8001`
