<hr>
<b>About the Goal Tracking Plugin</b><br>
<pre>

bug = http://forum.piwik.org/index.php?showtopic=150

The Goal Tracking Plugin is in alpha release. There is more coming soon!
- Test summary row works ok with subtables campaigns
- The Goal Report page will display conversion table by search engines, country, keyword, campaign, etc.
- The Goal Overview page will link to a Goal Report page with a "(more)" link that will ajax reload the page
- Goals could be triggered using javascript event, with custom revenue
- internationalization of all strings
- provide documentation, screenshots, blog post + add screenshot and inline help in "Add a New Goal"
- provide widgets for the dashboard, general goal overview, and one widget for each goal. With: graph evolution, sparklines. Widget with top segments for each goal.
- add visits with conversion sparkline in VisitsSummary overview
- link under goal conversion to full goal reports (optional display)

Known bugs
- Your top converting keyword include keyword without conversions
- The Goal total nb conversions should be sum of all goal conversions (wrong number when deleting a Goal)
- After adding goal, the window should refresh to the goal report page, and not to the dashboard
- Outlink trailing slash is automatically deleted from the URL, there would be a problem when trying to exact match a URL with trailing slash
- All graph labelling are not correct (always printing nb_uniq_visitors even when showing conversion or conversion_rate) see <a href='http://dev.piwik.org/trac/ticket/322'>#322</a>

Give us Feedback!
If you find any other bug, or if you have suggestions, please send us a message using the "Give us feedback" link at the top of the Piwik pages.

