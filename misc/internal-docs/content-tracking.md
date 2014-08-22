# Technical concept for implementing Content Tracking [#4996](#4996)

This is the technical concept for implementing content tracking. We won't plan anything to death but a little bit of thinking upfront makes sense :) Feel free to contribute and let us know if you have any objections! If your thoughts are not technical please comment on the actual issue [#4996](#4996).

## Naming
* Plugin name: Contents
* Content name - The name of the content visible in reports
* Content piece - eg a video file, image file, text, ...
* Content target - a clicked url, a started video, any "conversion" to be a bit more generic?

## Further Questions
1. Can the same one content piece has different names / targets? Can the same content name have different targets/pieces?
2. Are we always assuming the "conversion" or "target URL" is caused by a click or can it be a hover or drag/drop, ...? For a general solution we might want to assume it can be anything?
  * In this case we would also rename or need an additional attribute or whatever to [data-trackclick] etc.
3. Would a piece of content - such as a banner - have maybe custom variables etc?
4. How do we present the data in a report? Similar to events with second dimensions? Probably depends on 1)
5. I assume there can be nested content in theory. A piece of content that contains another piece of content. In this case we have to be careful when automatically picking name, target, ...
6. We would probably also need an attribute like data-target="$target" and/or the possiblity for data-trackclick="$target" since not all links might be defined via href but onclick javascript links
7. HTML Attributes always take precendence over css classes or the other way around (if both defined)?

## Tagging of the content piece declarative
In HTML...

## Tracking the impressions
Impressions are logically not really events and I don't think it makes sense to use them here. It would also make it harder to analyze events when they are mixed with pieces of content.

* Saving in database?
  * New column `id_action_content_url` and `id_action_content_piece` in `log_link_visit_action`. For name `id_action_name` can be reused?
  * Would we need a new column for each piece of content in action table to make archiver work? --> would result in many! columns
  * or would we need a new table for each piece of content to make archiver work? --> would be basically a copy of the link_action table and therefore not really makes sense I reckon. Only a lot of work. Logically I am not sure if an impression is actually an "action" so it could make sense
  * or would we store the pieces serialized as JSON in a `content` column? I don't know anything about the archiver but I think it wouldn't work at all
  * or would we create an action entry for each piece of content? --> yes I think! 
* New Action class that handles type content
* New url parameters like `c_p`, `c_n` and `c_u` for piece of content, name and url. Maybe instead of `c_u` would be better `c_t` for target which is more generic. Sending a JSON array would not work since we cannot log multiple actions in one tracking request. They have to be sent using bulk tracking instead.
* Only `c_n` would be required, `c_p` and `c_t` not as for instance a piece of content does not necessarily have a target (hard to measure a click ratio in this case?)


## Tracking the clicks
Contrary to impressions, clicks are actually events and it would be nice to use events here. Maybe we can link an event with a piece of content?

## Piwik.js
* We need to find all dom nodes having css class or html attribute.
 * Options for this is traversing over each node and checking for everything -> CSS selectors cannot be used on all browsers and it might be slow therefore -> maybe lot of work to make it cross browser compatible
 * https://github.com/fabiomcosta/micro-selector --> tiny selector library but does not support attributes
 * http://sizzlejs.com/ Used by jQuery & co but like 30kb (compressed + gzipped 4kb). Has way too many features we don't need
 * https://github.com/ded/qwery Doesn't support IE8 and a few others, no support for attribute selector
 * https://github.com/padolsey/satisfy 2.4KB and probably outdated
 * https://github.com/digitarald/sly very tiny and many features but last commit 3 years old
 * https://github.com/alpha123/Jaguar >10KB and last commit 2 years old
 * As we don't need many features we could implement it ourselves but probably needs a lot of cross-browser testing which I wanted to avoid. We'd only start with `querySelectorAll()` maybe. Brings also incredible [performance benefits](http://jsperf.com/jquery-vs-native-selector-and-element-style/2) (2-10 faster than jQuery) but there might be problems see http://stackoverflow.com/questions/11503534/jquery-vs-document-queryselectorall, http://jsfiddle.net/QdMc5/ and http://ejohn.org/blog/thoughts-on-queryselectorall/

## Reports
Nothing special here I think. We would probably automatically detect the type of content (image, video, text, sound, ...) depending on the content eg in case it ends with .jpg it could be recognized as image content and show a banner in the report.


## Order of implementation
Of course everything goes kinda in parallel:

* Make tracking of impressions work
* Make a report work
* Make tracking the clicks work
* Piwik.js and tagging of the content of pieces
