### Installation steps
1. Clone the repository:  
`git clone https://github.com/liqid11/testTask`  
`cd testTask`


2. Install Composer dependencies  
`docker run --rm -u "$(id -u):$(id -g)" -v $(pwd):/var/www/html -w /var/www/html laravelsail/php82-composer:latest composer install`  


3. Copy the .env configuration file  
`cp .env.example .env`  


4. Start the Docker containers:  
`./vendor/bin/sail up -d`  


5. Generate the application key  
`./vendor/bin/sail artisan key:generate`  


6. Run the database migrations:  
`./vendor/bin/sail artisan migrate --seed`  


### Once installed, the project will be available at:  
`http://localhost`  


### Run tests  
`./vendor/bin/sail artisan test`  


### API

* GET /users — Returns a paginated list of users.
* GET /users/{id} — Returns user details by ID.
* POST /users — Creates a new user.
* PUT /users/{id} — Updates user details by ID.
* DELETE /users/{id} — Deletes a user by ID.
