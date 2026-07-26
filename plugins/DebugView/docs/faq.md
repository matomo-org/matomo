## FAQ

__Why is Debug View not showing up in the Diagnostics menu?__

Debug View requires the Live plugin to be activated and the Visits Log to be
enabled. If the Visits Log was disabled system-wide or for the selected site
(Administration → General settings → Live), the menu entry is hidden.

__Why don't heartbeat/ping requests appear in the stream?__

Matomo heartbeats do not create separate actions in the visits log — they only
extend the time spent on the last pageview. Debug View streams the visits log,
so pings are reflected in the pageview's time-on-page rather than as own rows.

__Does Debug View store any additional data?__

Only while someone is actually watching Debug View, and only for tracking
requests explicitly flagged with the URL parameter `debug=1` (enable it in the
JavaScript tracker via `_paq.push(['enableDebugMode'])`): their raw URL
parameters are stored in the `debugview_raw_request` table so the details pane
can show exactly what was received. `token_auth` values are never stored. An
hourly scheduled task trims the table to the newest 500 entries per site;
capturing stops automatically a few minutes after the last open Debug View
page. Everything else is read from the existing visits log via the Live
plugin.
