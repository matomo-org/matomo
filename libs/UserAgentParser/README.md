# UserAgentParser

UserAgentParser is a php library to parse user agents,
and extracts browser name & version and operating system.

UserAgentParser is NOT designed to parse bots user agent strings; 
UserAgentParser will only be accurate when parsing user agents 
coming from Javascript Enabled browsers!

UserAgentParser is designed for simplicity, to accurately detect the
most used web browsers, and be regularly updated to detect new OS and browsers.

Potential limitations:

 * it does NOT detect sub sub versions, ie. the "5" in 1.4.5; this is a design decision to simplify the version number
 * it does NOT detect search engine, bots, etc. user agents; it's designed to detect browsers with javascript enabled
 * it does NOT detect nested UA strings caused by some browser add-ons

Feature request:

 * it could have the notion of operating system "types", ie "Windows". It currently only has "Windows XP", "Windows Vista", etc.

Feedback, patches: hello@piwik.org
