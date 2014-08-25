# Technical concept for implementing Content Tracking [#4996](#4996)

This is the technical concept for implementing content tracking. We won't plan anything to death but a little bit of thinking upfront makes sense :) Feel free to contribute and let us know if you have any objections! If your thoughts are not technical please comment on the actual issue [#4996](#4996).

## Tagging of the content piece declarative
In HTML...

## Tracking the impressions
Impressions are logically not really events and I don't think it makes sense to use them here. It would also make it harder to analyze events when they are mixed with pieces of content.

* New url parameter like `content` which contains serialized JSON like 
  [{"c": "content", "n": "name", "u": "url"}, ...]
* Saving in database?
  * New column `id_action_content_url` and `id_action_content_piece` in `log_link_visit_action`. For name `id_action_name` can be reused?
  * Would we need a new column for each piece of content in action table to make archiver work? --> would result in many! columns
  * or would we need a new table for each piece of content to make archiver work? --> would be basically a copy of the link_action table and therefore not really makes sense I reckon. Only a lot of work. Logically I am not sure if an impression is actually an "action" so it could make sense
  * or would we store the pieces serialized as JSON in a `content` column? I don't know anything about the archiver but I think it wouldn't work at all
  * or would we create an action entry for each piece of content? --> yes I think! 
* New Action class that handles type content

Would a piece of content have maybe custom variables etc?

## Tracking the clicks
Contrary to impressions, clicks are actually events and it would be nice to use events here. Maybe we can link an event with a piece of content?

## Piwik.js


## Reports
Nothing special here I think. We would probably automatically detect the type of content (image, video, text, sound, ...) depending on the content eg in case it ends with .jpg it could be recognized as image content and show a banner in the report.


## Order of implementation
Of course everything goes kinda in parallel:

* Make tracking of impressions work
* Make a report work
* Make tracking the clicks work
* Piwik.js and tagging of the content of pieces