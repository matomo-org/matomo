As a developer it may be useful to generate test data. Follow these steps:

* Install Piwik, for help see [Setting up Piwik](http://developer.piwik.org/guides/getting-started-part-1#getting-setup-to-extend-piwik)
* Install and activate the `VisitorGenerator` plugin via the Marketplace if needed
* Generate websites `./console visitorgenerator:generate-websites --limit=50`
* Generate users `./console visitorgenerator:generate-users --limit=50`
* Generate goals for a website `./console visitorgenerator:generate-goals --idsite=1`
* Generate visits for a website `./console visitorgenerator:generate-visits --idsite=1`
* Trigger the archiving in case browser archiving is disabled `./console core:archive`
