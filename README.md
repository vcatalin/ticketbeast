## tickebeast

### Test Driven Laravel Project

### Setup npm and and build the frontend
Run `npm install && npm run dev`. This will build your css and js required to render the available views properly.  

### Setup composer dependencies
Run `composer install`

### Running Laravel Dusk tests requires the following steps:  
Create sqlite test file for running the application with `touch database/sqlite.testing.database`  
Run the migrations with `php artisan migrate`  
Run the seeds with `php artisan db:seed --class=ConcertSeeder`  
Run your server with `php artisan serve --env=.env.dusk.local`   
Run the Laravel Dusk test suite using `php artisan dusk`  
Run all the tests with `vendor/bin/phpunit`
