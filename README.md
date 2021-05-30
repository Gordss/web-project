# Zip archive to CSV converter
A website that allows conversion from a zip archive to a CSV
format containing metadata about the files in the archive.

History of uploaded archives is persisted for every user

## There is an active deployment at [Heroku](https://fast-temple-25429.herokuapp.com/)

# How to start
4 environment variables need to be set in the php config:
* `DB_HOST` - host of a MySQL database ('default=localhost')
* `DB_NAME` - the name of the database (default='web_project')
* `DB_USER` - the database user's name (default='root')
* `DB_PASS` - the database user's password (default='')

Also, a database with name `DB_NAME` has to exist.
If local XAMPP setup is used, the only precondition is to create a database called
`web_project`. The default root user without password will be used.

## Logs
Error logs are generated in a file called `errors.log`, located in the root
folder of the project. If it does not exist, it is automatically generated when the first error is logged.