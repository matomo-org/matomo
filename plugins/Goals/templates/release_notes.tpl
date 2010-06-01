<div style="clear:both;"></div>
<br /><br /><hr />
<b>About the Goal Tracking Plugin</b><br />
<pre>
The Goal Tracking Plugin is in alpha release. There is more coming soon!

Give us Feedback!
If you find any other bug, or if you have suggestions, please send us a message using the "Give us feedback" link at the top of the Piwik pages.


Work left to do on the Goal Tracking plugin:
- The Goal Report page will display conversion table by search engines, country, keyword, campaign, etc.
- Contemplate adding goal conversions per landing page? If we add Goals per landing page, what page is used for goals that are triggered using piwikTracker.trackGoal in javascript?
- The Goal Overview page will link to a Goal Report page with a "(more)" link that will ajax reload the page
- Provide widgets for the dashboard, general goal overview, and one widget for each goal. With: graph evolution, sparklines. Widget with top segments for each goal.
- Add visits with conversion sparkline in VisitsSummary overview
- Add link under goal conversion to full goal reports (optional display)
- Internationalization of all strings i18n
- Provide documentation, screenshots, blog post + add screenshot and inline help in "Add a New Goal"
- N/A% should be n/a
- Way to test a URL against the regex
- Test summary row works ok with subtables campaigns
- Numeric records by the goal plugin can contain a lot of value=0 rows. Instead, we should only record the numeric value if it is not zero, and assume zero when not found. 
- The goal table, by segment, for example goal conversions by country, should really display number of conversions as well as the current "conversion rate" by goal. Having the absolute number for each country / search engine / etc. is critical.

Known bugs
- Outlink trailing slash is automatically deleted from the URL, there would be a problem when trying to exact match a URL with trailing slash
- lines with 0 visits and no conversion should not appear
- clicking on the graph for a given goal redirects to the dashboard instead of redirecting to the goal report for the clicked date
- see bug described in http://forum.piwik.org/index.php?showtopic=150

Feature requests
- need to clarify that goals are triggered once per visit max, but can be triggered multiple times by one unique visitor > need option to force only once per uniq visitor? (ie. e-commerce transaction)
- GeoIp compatibility, archive goals by city, country? see archiveDayAggregateGoals
- Goal conversions, revenue, etc. by hour
- I would like to be able to plot conversions, for a given keyword/website, over the last N days/weeks/etc. See	#534
- when entering the regex to detect as a goal, we could query the piwik API for this regex and list all URLs that match the regex; allows for an easy debug/check that the regex is correct and will be triggererd when expected

Refs #774