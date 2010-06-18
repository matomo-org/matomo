<div style="clear:both;"></div>
<br /><br /><hr />
<b>About the Goal Tracking Plugin</b><br />
<pre>
Work left to do on the Goal Tracking plugin:
- Add visits with conversion sparkline in VisitsSummary overview 	
- Provide documentation, screenshots, blog post + add screenshot and inline help in "Add a New Goal"
  Need to clarify that goals are triggered once per visit max, but can be triggered multiple 
  times by one unique visitor > need option to force only once per uniq visitor? (ie. e-commerce transaction)
- add UI tests in http://dev.piwik.org/trac/wiki/HowToTestUI
- GeoIp compatibility, archive goals by city, country? see archiveDayAggregateGoals

Feature requests
- Goal conversions, revenue, etc. by hour
- Way to test a URL against the regex : When entering the regex to detect as a goal, we could query 
  the piwik API for this regex and list all URLs that match the regex; allows for an easy debug/check 
  that the regex is correct and will be triggered when expected

Refs #774


Goals plugins improvements list


- Contemplate adding goal conversions per landing page? If we add Goals per landing page, 
what page is used for goals that are triggered using piwikTracker.trackGoal in javascript?
