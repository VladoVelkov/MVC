# Simple PHP MVC (currently backend only)

To use the MVC in your application you need PHP version 5.4 or higher. To install, download the zip archive and unpack. 
To test, set the configuration in example/app/config.php first and the use some tool or browser extension to create GET and POST requests.

Frontend part is still TBD. When implemented, MVC will be configured to use it or not.
This is the initial alpha version that still needs unit testing and error handling, so it is not recommended for use.

MODEL
- stores the data from user request or data selected from database,
- generates SQL queries using model data, meta, parameters and filters arrays,
- uses PDO to prepare statements and execute SQL queries in database

CONTROLLER
- tries to select data from cache first, if not there select from database and save to cache
- on changing data in database invalidate appropriate cache items
- provides response to user in array with parameters, data and errors

CACHE
- gets, sets and deletes cached data in JSON files

VALIDATOR
- validates user data in POST depending on fields defined for route and meta information 



# Example application 

Simple application that uses MVC backend can be found in example folder

.htaccess
- all valid routes and all valid query string parameters must be defined here

Pimple
- example application uses Pimple as dependency injection container / service provider
- external dependencies can be also saved in lib folder, like lib/Pimple

index.php
- Mod rewrite rules in .htaccess file maps all requests to index.php
- All configuration and request data parameters are stored in Pimple container
- Model, Cache, PDO and Controller object closure functions are added to Pimple container
- First step is to authenticate and authorize user (TBD in example application)
- If user is OK, then data provided must be validated first using Validator (GET parameters are validated in .htacccess)
- If validation is not OK, then prepare response for user
- If everything is OK, then controller action is called depending on module and action parameters
- Response is provided in JSON format having data, parameters and errors 

app/config.php
- contains global configuration parameters

app/meta.php
- contains meta information about each field in database

app/validation.php
- contains lists of fields to be validated in each route

language/en.php
- default English language file with codes and labels/messages
- other languages can be defined with translation of this file and saved in same folder 
- Multilanguage can be implemented in .htaccess file having /language / in URL definition

model
- specific models created for specific modules 
- inherit from Model class and override any method needed

cache
- folder for saving cached data

logs
- folder for saving log

database.sql
- MySQL database with categories and products used in example
