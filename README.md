# How to start project
1. git clone git@github.com:Vitalytar/currency-history.git
2. cd `<dir_where_cloned>`
3. `composer install`
4. Create new DB to install schemas
5. Create `.env.local` file in root or modify `.env` file and setup DB connection
6. Run migration - `php bin/console doctrine:migrations:migrate`
