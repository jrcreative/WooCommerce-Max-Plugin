# WordPress on Replit

Welcome to WordPress on Replit using HTTPD!
You will need to press "Use Template" if you have not already,
and then you can begin the installation!

You should probally private your repl so people can't view your database

## Installation

Switch to the shell tab and run:

```bash
bash init.sh
```

Then, go to start.sh and remove the first 2 lines

Press the run button, and then run
```bash
mysql
```
on the shell tab.

To create the database, run this SQL query:
```sql
CREATE DATABASE wordpress;
```

The database credentials are username "`runner`" and password "`NO`".
You can change the password if you want, but it will be stored in plain-text in wp-config.php