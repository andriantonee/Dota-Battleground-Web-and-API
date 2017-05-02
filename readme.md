## Software Requirement

- XAMPP or (PHP with version >= 5.6.4 & mySQL)
- Composer
- NodeJS
- Bower

## Installation

1. Clone this repository or Download the ZIP file.
```sh
$ git clone https://github.com/andriantonee/Dota-Battleground-Web-and-API.git project-directory
```
2. Then get into the project-directory.
```sh
$ cd project-directory
```
3. Copy & Paste **_.env.example_** file and Rename to **_.env_**.
4. Run **composer install** to install dependencies.
```sh
$ composer install
```
5. Generate Application Key.
```sh
$ php artisan key:generate
```
6. Create database in mySQL with name **dota_battleground**.
7. Run installation schema.
```sh
$ php artisan migrate
```
8. Generate Passport Key.
```sh
$ php artisan passport:key
```
9. Generate **CLIENT ID** for Passport Authentication.
```sh
$ php artisan passport:client --password --name="Laravel Password Grant Client"
```
10. Change **PASSPORT_CLIENT_ID** & **PASSPORT_CLIENT_SECRET** variable inside **_.env_** file using value from generated **CLIENT ID** above.
11. Run **bower install** to install CSS & JS dependencies.
```sh
$ bower install
```
12. Generate symlink folder using **php artisan**.
```sh
$ php artisan storage:link
```
