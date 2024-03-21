# How to start project
1. git clone git@github.com:Vitalytar/currency-history.git
2. cd `<dir_where_cloned>`
3. `composer install`
4. Create `.env.local` file in root or modify `.env` file and setup DB connection
5. Create new DB to install schemas (can be done just by running `bin/console doctrine:database:create` after configuring `.env`)
6. Run migration - `php bin/console doctrine:migrations:migrate`
7. Run `npm install`
8. Start local Symfony server, e.g. using Symfony CLI - `symfony server:start`
9. Start FE in dev mode - `npm run watch` / prod mode - `npm run build`
10. Fulfill DB with some initial data from API by running `bin/console app:fetch-rates-data` CLI command manually

For FE <br />
NPM 6.14.15 <br />
Node 14.18.1

# Cron job setup
To update data every day need to add following to the crontab

`0 1 * * * php /path/to/your/project/bin/console app:fetch-rates-data`

Current schedule will run every day at 1 AM
Schedule can be modified as per the requirements
