This a base for Debricked's backend home task. It provides a Symfony skeleton and a Docker environment with a few handy 
services:

- RabbitMQ
- MySQL (available locally at 3307, between Docker services at 3306)
- MailHog (UI available locally at 8025)
- PHP
- Nginx (available locally at 8888)

See .env for working credentials for RabbitMQ, MySQL and MailHog.

You can access the PHP environment's shell by executing `docker-compose exec php bash` in root folder.
We recommend that you always use the PHP container whenever you execute PHP, such as when installing and requiring new 
composer dependencies.
