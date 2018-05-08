## GeoIP 2 Databases for testing purpose

This folder contains our GeoIP 2 test databases. They are small and contain only those IPs that are used for testing. This speeds up tests as it's faster to parse a small database than download and parse on of Maxminds Lite DBs.


## Updating a database

In order to update a database e.g. add a new IP address with it's geo location you need to update `GeoIP2-City.json` and `GeoIP2-Country.json`. 
Afterwards run the PERL script `writeTestFiles.pl`. This script is based on a [script of Maxmind](https://github.com/maxmind/MaxMind-DB/blob/master/test-data/write-test-data.pl). More information about the script and the Maxmind database format can be found in [their repo](https://github.com/maxmind/MaxMind-DB/).
