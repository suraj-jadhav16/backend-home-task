## Running app with Postman
To upload multiple files using Postman to the http://localhost:8888/file/upload endpoint, follow these steps:
#### 1. Open Postman and create a new POST request.
#### 2. In the request URL, enter http://localhost:8888/file/upload and go to the Body tab and, the form-data option.
#### 3. Enter key field as `files[]`, and change the type to 'File' and attach the files.
        This endpoint accepts multiple files in a single request.
#### 4. Click the Send button to submit the request and check for response.

## Database
#### The database and tables will be created as soon as the app is launched
#### Use `rule_engine` Database which contains `scan_status` table.

## Cron Job
#### I have setup a cron job which runs every minute, so please expect the scan result/report email within 2 minutes.

## Mailbox
#### Use `http://localhost:8025/` to monitor the mailbox for emails.

## How to use the Docker environment
### Starting the environment
`docker compose up`

### Stopping the environment
`docker compose down`

### Running PHP based commands
You can access the PHP environment's shell by executing `docker compose exec php bash` (make sure the environment is up 
and running before, or the command will fail) in root folder.

We recommend that you always use the PHP container's shell whenever you execute PHP, such as when installing and 
requiring new composer dependencies.
