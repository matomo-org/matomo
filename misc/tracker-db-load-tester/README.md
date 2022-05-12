# Tracker DB Load Tester #

This standalone testing utility has been created to simulate the database queries that are performed
when a hit is tracked in Matomo. The generated visit logs contain randomised data.

It is intended for testing tracker insert performance but the resulting data can also be used for general 
testing.

It requires PHP with the MySQL PDO driver to run.

### Options are:

    Usage: php trackerLoadTester.php -d=[DB NAME] {-h=[DB HOST]} {-u=[DB USER]}
       {-p=[DB PASSWORD]} {-t=[DB TYPE]} {-r=[REQUEST LIMIT {-P=[DB PORT]}
       {-v=[VERBOSITY]}
    Options:
     -d          Database name, if 'random' then a randomly named database will automatically be created and used    
     -t          Database type, 'mysql' or 'tidb', used to adjust schema created with -d=random, defaults to 'mysql'
     -h          Database hostname, defaults to 'localhost', multiple hosts can be specified separated by commas and will be chosen randomly.
     -u          Database username, defaults to 'root''
     -p          Database password, defaults to none
     -P          Database port, defaults to 3306
     -r          Tracking requests limit, will insert this many tracking requests then exit, runs indefinitely if omitted
     -v          Verbosity of output [0 = quiet, 3 = show everything]
     -T          Throttle the number of requests per second to this value (experimental)
     -b          Basic test, do a very basic insert test instead of using tracker data 1=insert k/v, 2=select/insert
     -c          Create a new random database and tracking data schema only then exit
     -n          Percent of logged actions which will trigger a goal conversion, defaults to zero. Goals ids are 1..10     
     -m          Create x multiple headless test processes using the supplied parameters
     -ds         Start date in UTC for random visit/action date range, yyyy-mm-dd,hh:mm:ss
     -de         End date for random visit/action date, must be paired with -ds, if omitted then the current date is used
     --cleanup   Delete all randomly named test databases
     -rs         Create visits for random sites starting at this siteid
     -re         Create visits for random sites ending at this siteid

###Typical usage:

Run an endless test to insert tracker data into a new randomly named database using default connection options.

    php trackerDbLoadTester.php -d=random

Run a basic insert test using a new randomly named database against one of the three server endpoints provided.

    php trackerDbLoadTester.php -d=random -h=192.168.0.10,192.168.0.11,192.168.0.12 -d=random -u=root -b=1

Spawn 50 test processes, each performing queries against a separate random database:

    php trackerDbLoadTester.php -d=random  -h=192.168.0.10 -d=random -u=root -v=0 -m=50

Create a new test database and schema, then spawn 50 test processes to perform queries against the same database inserting 
visits with random dates in the 2021 year:

    php trackerDbLoadTester.php -d=random -h=192.168.0.10 -u=root -c

  Connected to the database server...
  Created new database 'tracker_db_test_051fa1f14e154d1a0245'...
  Create database only option is set, exiting now

    php trackerDbLoadTester.php -d=random -h=192.168.0.10 -u=root -d=tracker_db_test_051fa1f14e154d1a0245 \ 
    -u=root -v=0 -m=50 -ds=2021-01-01,00:00:00 -de=2021-12-31,23:59:59

Another approach is to install and configure a new installation of Matomo to create the schema and tables, then point 
the tool at the existing database. This would create 256 threads all insert visits into the same database.

    php trackerDbLoadTester.php -d=my_matomo -h=192.168.0.10 -u=root -p=password \ 
    -v=0 -m=256 -ds=2021-01-01,00:00:00 -de=2021-12-31,23:59:59

This command could be run on multiple load testing instances to increase testing throughput.
