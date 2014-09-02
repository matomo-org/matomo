# Technical concept for implementing Content Tracking [#4996](#4996)

This is the technical concept for implementing content tracking. We won't plan anything to death but a little bit of thinking upfront makes sense :) Feel free to contribute and let us know if you have any objections! If your thoughts are not technical please comment on the actual issue [#4996](#4996).

## Naming
* Plugin name: Contents
* Content name - The name of the content visible in reports
* Content piece - eg a video file, image file, text, ...
* Content target - a clicked url, a started video, any "conversion" to be a bit more generic?

## Open questions
* User can decide to manually setup the proper redirect URL via piwik.php?rec=1&idsite=1&clickurl={$URL_HERE}&....
  * Currently, the user would also have to add event URL parameters and make sure to send the correct name and piece to match an impression.
  * If the user does not use any data-content-* attributes this is very likely to fail since the auto detected content name and piece can easily change and tracking would be broken
  * The only advantage I see would be that we even track clicks if we haven't added a click listener to replace the URL yet (for instance before DOM loaded)
* and/or maybe we can replace the href="" directly within the DOM so right click, middle click, shift click are also tracked
  * sounds ok to me, have implement it like this. Only problem is in case a replaced link changes later for instance based on a visitor form selection.
     * To prevent this I added a click event on top of it and in case it does not start with configTrackerUrl I will build it again
  * it might be bad for SEO
  * FYI: outlinks/downloads will be still tracked as it is done currently for simplicity (500ms) so we are talking here only about internal links that are not anchor links (starting with "#"). Those would not be tracked
    * http://outlink.example.org --> not replaced -> handled the old way
    * #target --> not replaced -> handled the old way. In single page application users have to call trackWhatever again
      * note to myself: They should be able to parse a node that we parse for all content as you maybe wanna parse only the replaced ajax content. maybe v2
    * index.php, /foo/bar --> will be directly replaced by piwik.php in case clickNode (element having clickAttribute/Class) is an "A" element
    * Need to think about possible XSS. If an attacker can set href attributes on that website and we replace attribute based on that but should be ok ...

## Answered Questions
1. Can the same content piece have different names / targets? Can the same content name have different targets/pieces?

Maybe the unique ID of a Content can be the { Content name + Content piece }. Then we would recommend users to set the same Content target for a given tuple { Content name, Content piece }. 

I hope it makes sense to assume this tuple will have always same Content target by design?

In this case I would modify questionas as follows:
 * Can the same content piece have different names? Yes (eg. a banner image is used by different Content names),
 * Can the same { content name, content piece } have different targets? Yes, but it's not recommended: Piwik will only aggregate one content target value. (eg. keep the latest content target value tracked for this { content name, content piece } tuple on a given day)


2. Are we always assuming the "conversion" or "target URL" is caused by a click or can it be a hover or drag/drop, ...? For a general solution we might want to assume it can be anything?
  * In this case we would also rename or need an additional attribute or whatever to [data-trackclick] etc.

When drag and dropping there is a click needed by user, so maybe `data-trackclick` would still be OK in this case?
if  you have better naming idea feel free to suggest. Or maybe you have other use cases besides clicks and drag n drop?

3. Would a piece of content - such as a banner - have maybe custom variables etc?

It would be nice to be able to set custom variables to Contents. 

One possible use case is A/B testing. Maybe it would make sense to use Contents plugin for A/B testing. We could measure Content name = Experiment_TopMenu, Content piece = http://host/a.jpg. In a custom variable we would store "experiment => B". Then we would know that the given experiment is called Experiment_TopMenu and is defined by the image and that it's the variant B being served.

4. How do we present the data in a report? Similar to events with second dimensions? Probably depends on 1)

Second dimension would be really powerful to have (as per suggestion in 1)). It would let user see different banner images for a given banner name.

There would be two reports: 
 * First dimension: Banner Names, Second dimension: Banner pieces
 * First dimension: Banner pieces, Second dimension: Banner names

(It's a bit simpler than Events because we don't need to switch the second dimension.)
 
5. I assume there can be nested content in theory. A piece of content that contains another piece of content. In this case we have to be careful when automatically picking name, target, ...

Nested content makes sense (users will do this). How would it work when several contents are nested?
Note: we don't need to handle this case in MVP but maybe worth thinking about it.

6. FYI: We would probably also need an attribute like data-target="$target" and/or the possiblity for data-trackclick="$target" since not all links might be defined via href but onclick javascript links. See next section

+1

7. HTML Attributes always take precendence over css classes or the other way around (if both defined)? I think attributes should take precendence which I think is also defined in the spec

attributes take precedence over CSS classes

8. Do we need to support IE7 and older? Firefox 3 and older?

Support modern browsers is enough (ie. last 2 years or so?).

9. "Maybe we could automatically detect when such element becomes visible, and send the Impression event automatically"
  * I think we can detect whether a specific content was visible at a specific time in most cases but not necessarily automatically. We would have to check the DOM for this every few ms (in case of Carousel) and we'd also have to attach to events like scrolling etc. This can make other peoples website slow, especially on mobile but even browser. Website owners usually want to achieve 60fps to have animations and scrolling smooth and they usually invest a lot of time to achieve this. So it has to an opt-in if at all

in case user tags an element with `data-noautotrack` then it's already a kind of opt-in by user, so maybe in this case it's acceptable to check whether element tagged is visible, eg. every 500 ms ?

  * Do I understand it right that we send an impression only if it is visible?

Yes.

    * We'd probably have to offer a mode to send all banners independend of visibility

Sounds good: this would make Contents plugin more generic.


    * We'd probably have to offer a mode to rescan all banners again at a certain time and only track those content pieces now that were not visibile before but are now

In ticket I wrote `function trackContentPieces() that will let users re-scan the page for Content pieces when DOM has changed.` but maybe instead the function should be called `rescanPageForContents` ?

    * We'd probably have to offer a method to pass a DOM node and track it independent of visibility (useful for instance in case of carousel when the website owner already knows a specific content piece is visible now but does not want to use expensive events for this)

if I understand correctly it would make life of JS developers easier by providing nicer APIs to them?

so +1

    * We'd maybe have to offer a mode where we are trying to detect automatically when an impression becomes visible and send it

I think that should be the default mode, ie. on page load we detect impressions, and then we also attach to events like scrolling to check ie. every 500ms whether a given Contents is visible. Would that be work?

10. FYI: "you may add a CSS class or attribute to the link element to track" => It could be also a span, a div or something else
11. FYI: There is way to much magic how content-name is found and it is neither predicatble nor understandable by users, I will simplify this and rather require users to set specific attributes! See next section

OK

12. FYI: We need to define how a content piece is defined in markup since it can be anything (was something like piwik-banner before) see next section

## Tagging of the content piece declarative

Namings:
* `[data-track-content] or .piwikTrackContent` == mark content element
* `[data-content-name]` == set content name
* `[data-content-piece] or .piwikContentPiece` == mark content piece element
* `[data-content-interaction] or .piwikContentInteraction` == mark click element

### New proposed way of finding content
 * Search for any `data-track-content` attribute and `piwikTrackContent` CSS class within the DOM
 * One page can use both ways so we will mix the result of found contents

### New proposed way of finding content name

* Search for any `data-content-name` attribute within the content (`.piwikTrackContent` and children)
* Use value of content-piece in case there is one (which will discover src of image, video and audio if possible). In case it includes a domain we will remove it? TODO Maybe should remove domain only if != current location.domain
* Seach for `title` attribute in `.piwikTrackContent`
* Seach for `title` attribute in `.piwikContentPiece`
* Seach for `title` attribute in `.piwikContentInteraction`
* If neither found use "Unknown"
* Note: We will always trim the chosen name

Matthieu feedback:

> Use value of content-piece in case there is one

Maybe only the path of the content-piece or even only the content-piece filename should be used? --> Yes, good one!

> If neither found ignore content or use "Unknown"

Before ignoring content or setting content-name to "Unknown", maybe it would be user friendly to read the first "title" attribute found in `.piwikTrackContent` (and its children). --> Not in children but otherwise yes see above!

(But I'm not pushing this since it's not that important, if you think it's better to have only clear attribute names then I'm OK with it... As long as we document things clearly then it will be fine for users)

TODO document how we find contentPieceNode, contentTargetNode,...

### New proposed way of finding content target
 * Search for any `data-content-interaction` attribute with a value in the content (`.piwikTrackContent` and children)
 * search for `href` attribute in element with attribute `data-content-interaction` (if attribute has no value)
 * search for `href` attribute in element with css class `.piwikContentInteraction` in case there is no element having attribute `data-tracking-interaction`
 * search for `href` attribute in contentPieceNode
* Note: We will always trim the chosen target

### New proposed way of finding content piece
 * Search for any `data-content-piece` attribute with value within the content (`.piwikTrackContent` and children)
 * if `.piwikTrackContent` is image, audio or video we will try to find a source (difficult for video)
 * if `.piwikContentInteraction` is image, audio or video we will try to find a source (difficult for video)
 * if `.piwikTrackContent` and `.piwikContentInteraction` is not image or video we will use `text()` of this element??? (I think we better ignore this step, text can be a lot and problems with i18n makes it not useful at all?)
 * Note: source of image/video and any text() that we detect automatically can be complicated in case of i18n, it will be recommended to use data-track-content attribute
* Note: We will always trim the chosen content piece
 


> text can be a lot and problems with i18n makes it not useful at all

What problems are there with i18n, eg. when the page is in UTF-8? eg. when testing on google.cn to read `$('#addlang').textContent` it shows the content OK.


### A few Examples
```
<img src="http://www.example.com/path/xyz.jpg" href="/" data-track-content />
// content name   = /path/xyz.jpg
// content piece  = http://www.example.com/xyz.jpg
// content target = /
// TODO SHOULD WE ADD THE DOMAIN AND IN CASE OF A RELATIVE URL THE PATH AUTOMATICALLY TO "/" in content target?

<img src="xyz.jpg" href="/" class="piwikTrackContent"/>
// content name   = xyz.jpg
// content piece  = xyz.jpg
// content target = /

<img src="xyz.jpg" href="/" data-track-content data-content-name="My Content Name" data-content-piece="The Content Piece" data-content-interaction="http://www.example.com"/>
// content name   = My Content Name
// content piece  = The Content Piece
// content target = http://www.example.com

<img src="xyz.jpg" href="javascript:..." data-track-content data-content-interaction="/"/>
<img src="xyz.jpg" onclick="..."         data-track-content data-content-interaction="/"/>
// content name   = xyz.jpg
// content piece  = xyz.jpg
// content target = /

<div data-track-content data-content-name="banner ad 1"><a href="/" data-content-interaction>click here to foo bar</a></div>
// content name   = banner ad 1
// content piece  = ""
// content target = /

<div data-track-content data-content-name="banner ad 1"><a href="/" data-content-interaction data-content-piece="xyz ad">click here to foo bar</a></div>
// content name   = banner ad 1
// content piece  = xyz ad
// content target = /

<div data-track-content data-content-name="banner ad 1" data-content-interaction="/" data-content-piece="xyz.jpg"><img src="xyz.jpg"/></div>
// content name   = banner ad 1
// content piece  = xyz.jpg
// content target = /

<div data-track-content data-content-interaction="/"><img src="xyz.jpg" data-content-piece/></div>
// content name   = xyz.jpg
// content piece  = xyz.jpg
// content target = /

<div data-track-content><img src="xyz.jpg" data-content-piece/></div>
// content name   = xyz.jpg
// content piece  = xyz.jpg
// content target = ""

```

## Tracking the impressions
Impressions are logically not really events and I don't think it makes sense to use them here. It would also make it harder to analyze events when they are mixed with pieces of content.

* Saving in database?
  * New column `idaction_content_url` and `idaction_content_piece` in `log_link_visit_action`. For name `idaction_name` can be reused?

Could we also reuse `idaction_url` instead of adding new column `idaction_content_url`?
And we could also store the URL of the page showing the Content in `idaction_url_ref`. (reusing columns is good in this case)

  * Would we need a new column for each piece of content in action table to make archiver work? --> would result in many! columns
  * or would we need a new table for each piece of content to make archiver work? --> would be basically a copy of the link_action table and therefore not really makes sense I reckon. Only a lot of work. Logically I am not sure if an impression is actually an "action" so it could make sense
  * or would we store the pieces serialized as JSON in a `content` column? I don't know anything about the archiver but I think it wouldn't work at all
  * or would we create an action entry for each piece of content? --> yes I think! 

Yes it seems most logical to create an action entry for each Content.

* New Action class that handles type content
* New url parameters like `c_p`, `c_n` and `c_u` for piece of content, name and url. Maybe instead of `c_u` would be better `c_t` for target which is more generic. Sending a JSON array would not work since we cannot log multiple actions in one tracking request. They have to be sent using bulk tracking instead.
 * Bulk tracking in JS is covered in: [#4910](https://github.com/piwik/piwik/issues/4910)

* Only `c_n` would be required, `c_p` and `c_t` not as for instance a piece of content does not necessarily have a target (hard to measure a click ratio in this case?)

OK

## Tracking the clicks
Contrary to impressions, clicks are actually events and it would be nice to use events here. Maybe we can link an event with a piece of content?

Linking the Click event to the piece of content will be hard, since we don't know when tracking the event, the idaction of the Content piece impression. I think only way is store in Event the two Unique IDs dimensions { Content name, Content piece }. For example the Click Event could be:
 * Event Category = `Content Click`
 * Event Action = `$Content_piece`
 * Event Name = `$Content_Name`

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


Matt: I don't have much thoughts on the JS part, besides that we don't need to have amazing support of old browsers. ie. browsers released in the last two or three years would be perfect.

## Reports
Nothing special here I think. We would probably automatically detect the type of content (image, video, text, sound, ...) depending on the content eg in case it ends with [.jpg, .png, .gif] it could be recognized as image content and show a banner in the report.


## Order of implementation
Of course everything goes kinda in parallel:

* Make tracking of impressions work
* Make a report work
* Make tracking the clicks work
* Piwik.js and tagging of the content of pieces
