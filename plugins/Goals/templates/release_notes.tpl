<div style="clear:both" />
<br><br><hr>
<b>About the Goal Tracking Plugin</b><br>
<pre>
The Goal Tracking Plugin is in alpha release. There is more coming soon!

Give us Feedback!
If you find any other bug, or if you have suggestions, please send us a message using the "Give us feedback" link at the top of the Piwik pages.

Work left to do on the Goal Tracking plugin:
- Test summary row works ok with subtables campaigns
- The Goal Report page will display conversion table by search engines, country, keyword, campaign, etc.
- The Goal Overview page will link to a Goal Report page with a "(more)" link that will ajax reload the page
- Goals could be triggered using javascript event, with custom revenue
- provide widgets for the dashboard, general goal overview, and one widget for each goal. With: graph evolution, sparklines. Widget with top segments for each goal.
- add visits with conversion sparkline in VisitsSummary overview
- link under goal conversion to full goal reports (optional display)
- N/A% should be n/a
- internationalization of all strings i18n
- provide documentation, screenshots, blog post + add screenshot and inline help in "Add a New Goal"
- way to test a URL against the regex
- contemplate adding goal conversions per landing page?

Known bugs
- see bug described in http://forum.piwik.org/index.php?showtopic=150
- Your top converting keyword include keyword without conversions?
- The Goal total nb conversions should be sum of all goal conversions (wrong number when deleting a Goal) 
- After adding goal, the window should ideally refresh to the goal report page, and not to the dashboard
- Outlink trailing slash is automatically deleted from the URL, there would be a problem when trying to exact match a URL with trailing slash
- lines with 0 visits and no conversion should not appear

Feature requests
- need to clarify that goals are triggered once per visit max, but can be triggered multiple times by one unique visitor > need option to force only once per uniq visitor? (ie. e-commerce transaction)
- GeoIp compatibility, archive goals by city, country? see archiveDayAggregateGoals
- Goal conversions, revenue, etc. by hour
- I would like to be able to plot conversions, for a given keyword/website, over the last N days/weeks/etc. See	#534
- when entering the regex to detect as a goal, we could query the piwik API for this regex and list all URLs that match the regex; allows for an easy debug/check that the regex is correct and will be triggererd when expected
