# Technical concept for implementing Content Tracking [#4996](#4996)

See https://github.com/piwik/piwik/issues/4996 for explanation of the actual feature.

This is the technical concept for implementing content tracking. We won't plan anything to death but a little bit of thinking upfront makes sense :) Feel free to contribute and let us know if you have any objections! If your thoughts are not technical please comment on the actual issue [#4996](#4996).

## Naming
| Name  | Purpose |
| ------------- | ------------- |
| Plugin name | Contents |
| Content block | Is a container which consists of a content name, piece, target and an interaction. |
| Content name | A name that represents a content block. The name will be visible in reports. One name can belong to differnt content pieces. |
| Content piece | This is the actual content that was displayed, eg a path to a video/image/audio file, a text, ... |
| Content target | For instance the URL of a landing page where the user was led to after interacting with the content block. |
| Content impression | Any content block that was displayed on a page, such as a banner or an ad. Optionally you can tell Piwik to track only impressions for content blocks that were actually visible. |
| Content interaction | Any content block that was interacted with by a user. This means usually a 'click' on a banner or ad happened, but it can be any interaction. |
| Content interaction rate | The ratio of content impressions to interactions. For instance an ad was displayed a 100 times and there were 2 interactions results in a rate of 2%. |

## Tracking the content declarative

Generally said you can usually choose between HTML attributes and CSS classes to define the content you want to track. Attributes always take precedence over CSS classes. So if you define an attribute on one element and a CSS class on another element we will always pick the element having the attribute. If you set the same attribute or the same class on multiple elements within one block, the first element will always win.
Nested content blocks are currently not supported.

HTML attributes are the recommended way to go as it allows you to set a specific value that will be used when detecting the content impressions on your website.
Imagine you do not have a value for an HTML attribute provided or if a CSS class is used, we will have to try to detect the content name, piece and target automatically based on a set of rules which are explained further below. For instance we are trying to read the content target from a `href` attribute of a link, the content piece from a `src` attribute of an image, and the name from a `title` attribute.
If you let us automatically detect those values it can influence your tracking over time. For instance if you provide the same page in different languages, and we will detect the content automatically, we might end up in many different content blocks that represent actually all the same. Therefore it is recommended to use the HTML-attributes including values.

The following attributes and their corresponding CSS classes are used which will be explained in detail below:
* `[data-track-content] or .piwikTrackContent` == Defines a content block
* `[data-content-name=""]` == Defines the name of the content block
* `[data-content-piece=""] or .piwikContentPiece` == Defines the content piece
* `[data-content-target=""] or .piwikContentTarget` == Defines the content target
* `[data-content-ignoreinteraction] or .piwikContentIgnoreInteraction` == Tells Piwik to not automatically track the interaction

### How to define a block of content?
You can use either the attribute `data-track-content` or the CSS class `piwikTrackContent`. The attribute does not require any value.

Examples:
```
<img src="img-en.jpg" data-track-content/>
// content name   = absolutePath(img-en.jpg)
// content piece  = absoluteUrl(img-en.jpg)
// content target = ""

<img src="img-en.jpg" class="piwikTrackContent"/>
// content name   = absolutePath(img-en.jpg)
// content piece  = absoluteUrl(img-en.jpg)
// content target = ""
```

As you can see in these examples we do detect the content piece and name automatically based on the `src` attribute of the image. The content target cannot be detected since an image does not define a link.

Note: In the future we may allow to define the name of the content using this attribute instead of `data-content-name` but I did not want this for two reasons: It could also define the actual content (the content piece) so it would not be intuitive, using `data-content-name` attribute allows to set the name also on nested attributes.

### How do we detect the content piece element?
The content piece element is used to detect the actual content of a content block.

To find the content piece element we will try to find an element having the attribute `data-content-piece` or the CSS class `piwikContentPiece`. This attribute/class can be specified anywhere within a content block.
If we do not find any specific content piece element, we will use the content block element.

### How do we detect the content piece?

* The simplest scenario is to provide an HTML attribute `data-content-piece="foo"` including a value anywhere within the content block or in the content block element itself.
* If there is no such attribute we will check whether the content piece element is a media (audio, video, image) and we will try to detect the URL to the media automatically. For instance using the `src` attribute. If a found media URL does not include a domain or is not an absolute URL we will make sure to have a fully qualified URL.
  * In case of video and audio elements, when there are multiple sources defined, we will choose the URL of the first source
* If we haven't found anything we will fall back to use the value "Unknown". In such a case you should set the attribute `data-content-piece` telling us explicitly what the content is.

Examples:
```
<a href="http://www.example.com" data-track-content><img src="img-en.jpg" data-content-piece="img.jpg"/></a>
// content name   = img.jpg
// content piece  = img.jpg
// content target = http://www.example.com
```
As you can see we can now define a specific value for the content piece which can be useful if your text or images are different in for each language.
This time we can also automatically detect the content target since we have set the content block on an `a` element. More about this later. The `data-content-piece` attribute can be set on any element, also in the `a` element.

```
<a href="http://www.example.com" data-track-content><img src="img-en.jpg" data-content-piece/></a>
<a href="http://www.example.com" data-track-content><img src="img-en.jpg" class="piwikContentPiece"/></a>
// content name   = absolutePath(img-en.jpg)
// content piece  = absoluteUrl(img-en.jpg)
// content target = http://www.example.com
```

In this example we were able to detect the name and the piece of the content automatically based on the `src` attribute.

```
<a href="http://www.example.com" data-track-content><p data-content-piece>Lorem ipsum dolor sit amet</p></a>
<a href="http://www.example.com" data-track-content><p class="piwikContentPiece">Lorem ipsum dolor sit amet</p></a>
// content name   = Unknown
// content piece  = Unknown
// content target = http://www.example.com
```

As the content piece element is not an image, video or audio we cannot detect the content automatically. In such a case you have to define the `data-content-piece` attribute and set a value to it. We do not use the text of this element by default since the text might change often resulting in many content pieces, since it can be very long, since it can be translated and therefore results in many different content pieces although it is always the same, since it might contain user specific content and so on.

Better:
```
<a href="http://www.example.com" data-track-content><p data-content-piece="My content">Lorem ipsum dolor sit amet...</p></a>
// content name   = My content
// content piece  = My content
// content target = http://www.example.com
```

### How do we detect the content name?
The content name represents a content block which will help you in the Piwik UI to easily identify a specific block.

* The simplest scenario is that you provide us an HTML attribute `data-content-name` with a value anywhere within a content block or in a content block element itself.
* If there is no such element we will use the value of the content piece in case there is one (if !== Unknown).
  * A content piece will be usually detected automatically in case the content piece is an image, video or audio element.
  * If content piece is a URL that is identical to the current domain of the website we will remove the domain from the URL
* If we do not find a name we will look for a `title` attribute in the content block element.
* If we do not find a name we will look for a `title` attribute in the content piece element.
* If we do not find a name we will look for a `title` attribute in the content target element.
* If we do not find a name we will fall back to "Unknown"

Examples:
```
<img src="img-en.jpg" data-track-content data-content-name="Image1"/>
// content name   = Image1
// content piece  = absoluteUrl(img-en.jpg)
// content target = ""
```

This example would be the way to go by defining a `data-content-name` attribute anywhere we can easily detect the name of the content.

```
<img src="img-en.jpg" data-track-content/>
// content name   = absolutePath(img-en.jpg)
// content piece  = absoluteUrl(img-en.jpg)
// content target = ""
```

If no content name is set, it will default to the content piece in case there is one.

```
<img src="http://www.example.com/path/img-en.jpg" data-track-content/>
// content name   = /path/img-en.jpg
// content piece  = http://www.example.com/path/img-en.jpg
// content target = ""
```

If content piece contains a domain that is the same as the current website's domain we will remove it

```
<a href="http://www.example.com" data-track-content>Lorem ipsum dolor sit amet...</p></a>
// content name   = Unknown
// content piece  = Unknown
// content target = http://www.example.com
```

In case there is no content name, no content piece and no title set anywhere it will default to "Unknown". To get a useful content name you should set either the `data-content-name` or a `title` attribute.

```
<a href="http://www.example.com" data-track-content title="Block Title"><span title="Inner Title" data-content-piece>Lorem ipsum dolor sit amet...</span></a>
// content name   = Block Title
// content piece  = Unknown
// content target = http://www.example.com
```

In case there is no content name and no content piece we will fall back to the `title` attribute of the content block. The `title` attribute of the block element takes precendence over the piece element in this example.

### How do we detect the content target element?
The content target is the element that we will use to detect the URL of the landing page of the content block. The target element is usually a link or a button element. Generally said the target doesn't have to be a URL it can be anything but in most cases it will be a URL. A target could be for instance also a tab-container

We detect the target element either by the attribute `data-content-target` or by the class `.piwikContentTarget`. If no such element can be found we will fall back to the content block element.

### How do we detect the content target URL?

* The simplest scenario is that you provide us an HTML attribute `data-content-target` with a value anywhere within a content block or in a content block element itself.
* If there is no such element we will look for an `href` attribute in the target element
* If there is no such attribute we will use an empty string ""

Examples:
```
<a href="http://www.example.com" data-track-content>Click me</a>
// content name   = Unknown
// content piece  = Unknown
// content target = "http://www.example.com"
```

As no specific target element is set, we will read the `href` attribute of the content block.

```
<a onclick="location.href='http://www.example.com'" data-content-target="http://www.example.com" data-track-content>Click me</a>
// content name   = Unknown
// content piece  = Unknown
// content target = "http://www.example.com"
```

No `href` attribute is used as the link is executed via javascript. Therefore a `data-content-target` attribute with value has to be specified.


```
<div data-track-content><input type="submit"/></div>

// content name   = Unknown
// content piece  = Unknown
// content target = ""
```

As there is neither a `data-content-target` attribute nor a `href` attribute we cannot detect the target.

```
<div data-track-content><input type="submit" data-content-target="http://www.example.com"/></div>

// content name   = Unknown
// content piece  = Unknown
// content target = "http://www.example.com"
```

As the `data-content-target` attribute is specifically set with a value, we can detect the target URL based on this. Otherwise we could not.

```
<div data-track-content><a href="http://www.example.com" data-content-target>Click me</a></div>
<div data-track-content><a href="http://www.example.com" class="piwikContentTarget">Click me</a></div>
// content name   = Unknown
// content piece  = Unknown
// content target = "http://www.example.com"
```

As the target element has a `href` attribute we can detect the content target automatically.

### How do we track an interaction automatically?

Interactions can be detected declarative in case the detected target element is an `a` and `area` element with an `href` attribute. If not, you will have to track
the interaction programmatically, see one of the next sections. We generally treat links to the same page differently than downloads or outlinks.

We use `click` events do detect an interaction with a content. On mobile devices you might want to listen to `touch` events. In this case you may have to disable automatic content interaction tracking see below.

#### Links to the same domain
In case we detect a link to the same website we will replace the current `href` attribute with a link to the `piwik.php` tracker URL. Whenever a user clicks on such a link we will first send the user to the `piwik.php` of your Piwik installation and then redirect the user from there to the actual page. This click will be tracked as an event. Where the event category is the string `Content`, the event action is the value of the content interaction such as `click` and the event name will be the same as the content name.

If the URL of the replaced `href` attribute changes meanwhile by your code we will respect the new `href` attribute and make sure to update the link with a `piwik.php` URL. Therefore we will add a `click` listener to the element.

Note: The referrer information will get lost when redirecting from piwik.php to your page. If you depend on this you need to disable automatic tracking of interaction see below

If you have added an `href` attribute after we scanned the DOM for content blocks we can not detect this and an interaction won't be tracked.

#### Outlinks and downloads
Outlinks and downloads are handled as before. If a user clicks on a download or outlink we will track this action using an XHR. Along with the information of this action we will send the information related to the content block. We will not track an additional event for this.

#### Anchor links
Anchor links will be tracked using an XHR.

### How to prevent the automatic tracking of an interaction?

Maybe you do not want us to track any interaction automatically as explained before.
To do so you can either set the attribute `data-content-ignoreinteraction` or the CSS class `piwikContentIgnoreInteraction` on the content target element.

Examples
```
<a href="http://outlink.example.com" class="piwikTrackContent piwikContentIgnoreInteraction">Add to shopping cart</a>
<a href="http://outlink.example.com" data-track-content data-content-ignoreinteraction>Add to shopping cart</a>
<div data-track-content><a href="http://outlink.example.com" data-content-target data-content-ignoreinteraction>Add to shopping cart</a></div>
```

In all examples we would track the impression automatically but not the interaction.

Note: In single page application you will most likely always have to disable automatic tracking of an interaction as otherwise a page reload and a redirect will happen.

### Putting it all together

A few Examples:

```
<div data-track-content data-content-name="My Ad">
    <img src="http://www.example.com/path/xyz.jpg" data-content-piece />
    <a href="/anylink" data-content-target>Add to shopping cart</a>
</div>
// content name   = My Ad
// content piece  = http://www.example.com/path/xyz.jpg
// content target = /anylink
```

A typical example for a content block that displays an image - which is the content piece - and a call to action link - which is the content target - below.
We would replace the `href=/anylink` with a link to piwik.php of your Piwik installation which will in turn redirect the user to the actual target to actually track the interaction.

```
<a href="http://ad.example.com" data-track-content>
    <img src="http://www.example.com/path/xyz.jpg" data-content-piece />
</a>
// content name   = /path/xyz.jpg
// content piece  = http://www.example.com/path/xyz.jpg
// content target = http://ad.example.com
```

A typical example for a content block that displays a banner ad.

```
<a href="http://ad.example.com" data-track-content data-content-name="My Ad">
    Lorem ipsum....
</a>
// content name   = My Ad
// content piece  = Unknown
// content target = http://ad.example.com
```

A typical example for a content block that displays a text ad.

## Tracking the content programmatically

There are several ways to track a content impression and/or interaction manually, semi-automatically and automatically. Please be aware that content impressions will be tracked using bulk tracking which will always send a `POST` request, even if `GET` is configured which is the default.

Note: In case you have link tracking enabled you should call `enableLinkTracking()` before any of those functions.

#### `trackAllContentImpressions()`

You can use this method to scan the entire DOM for content blocks.
For each content block we will track a content impression immediately. If you only want to track visible content impression have a look at `trackVisibleContentImpressions()`.

Note: We will not send an impression of the same content block twice if you call this method multiple times unless `trackPageView()` is called meanwhile. This is useful for single page applications. The "same" content blocks means if a content block has the identical name, piece and target as an already tracked one.
Note: At this stage we do not exeute this method automatically along with a trackPageView(), we can do this later once we know it works

#### `trackVisibleContentImpressions(checkOnSroll, timeIntervalInMs)`
If you enable to track only visible content we will only track an impression if a content block is actually visible. With visible we mean the content block has been in the view port, it is actually in the DOM and is not hidden via CSS (opacity, visibility, display, ...).

* Optionally you can tell us to rescan the DOM automatically after each scroll event by passing `checkOnSroll=true`. We will then check whether the previously hidden content blocks are visible now and if so track the impression.
  * Parameter defaults to boolean `true` if not specified.
  * As the scroll event is triggered after each pixel scrolling would be very slow when checking for new visible content blocks each time the event is triggered. Instead we are checking every 100ms whether a scroll event was triggered and if so we scan the DOM for new visible content blocks
  * Note: If a content block is placed within a scrollable element (`overflow: scroll`), we do currently not attach an event in case the user scrolls within this element. This means we would not detect that such an element becomes visible.
* Optionally you can tell us to rescan the entire DOM for new impressions every X milliseconds by passing `timeIntervalInMs=500` (rescan DOM every 500ms).
  * If parameter is not set, a default interval sof 750ms will be used.
  * Rescanning the entire DOM and detecting the visible state of content blocks can take a while depending on the browser and amount of content
  * We do not really rescan every X milliseconds. We will schedule the next rescan after a previous scan has finished. So if it takes 20ms to scan the DOM and you tell us to rescan every 50ms it can actually take 70ms.
  * In case your frames per second goes down you might want to increase this value
* If you do want to only track visible content but not want us to perform any checks automatically you can either call `trackVisibleContentImpressions()` manually at any time to rescan the entire DOM or `trackContentImpressionsWithinNode()` to check only a specific part of the DOM for visible content blocks.
 * Call `trackVisibleContentImpressions(false, 0)` to initially track only visible content impressions
 * Call `trackVisibleContentImpressions()` at any time again to rescan the entire DOM for newly visible content blocks or
 * Call `trackContentImpressionsWithinNode(node)` at any time to rescan only a part of the DOM for newly visible content blocks

Note: You can not change the `checkOnScroll` or `timeIntervalInMs` after this method was called the first time.

#### `(checkOnSroll, timeIntervalInMs)`

Is a shorthand for calling `enableTrackOnlyVisibleContent()` and `trackContentImpressions()`.

#### `trackContentImpressionsWithinNode(domNode, contentTarget)`

You can use this method if you, for instance, dynamically add an element using JavaScript to your DOM after the we have tracked the initial impressions. Calling this method will make sure an impression will be tracked for all content blocks contained within this node.

Example
```
var div = $('<div>...<div data-track-content>...</div>...<div data-track-content>...</div></div>');
$('#id').append(div);

_paq.push(['trackContentImpressionsWithinNode', div[0]]);
```

We would detect two new content blocks in this example.

Please note: In case you have enabled to only track visible content blocks we will respect this. In case it contains a content block that was already tracked we will not track it again.

#### trackContentInteractionNode(domNode, contentInteraction)

By default we track interactions depending on a click and sometimes we cannot track interactions automatically add all. See "How do we track an interaction automatically?". In case you want to track an interaction manually for instance on a double click or on a form submit you can do this as following:

Example
```
anyElement.addEventListener('dblclick', function () {
    _paq.push(['trackContentInteractionNode', this]);
});
form.addEventListener('dblclick', function () {
    _paq.push(['trackContentInteractionNode', this, 'submittedForm']);
});
```

* The passed `domNode` can be any node within a content block or the content block element itself. Nothing will be tracked in case there is no content-block found.
* The content name and piece will be detected based on the content block
* Optionally you can set the name of the content interaction. If none is provided the `Unknown` will be used. Could be for instance `click` or `submit`.
* The interaction will actually only have any effect if an impression was tracked for this content-block

#### `trackContentImpression(contentName, contentPiece, contentTarget)` and `trackContentInteraction(contentName, contentPiece, contentInteraction)`
You should use those methods only in conjunction together. It is not recommended to use `trackContentInteraction()` after an impression was tracked automatically using on of the other methods as an interaction would only count if you do set the same content name and piece that was used to track the related impression.

Example
```
_paq.push(['trackContentImpression', 'Content Name', 'Content Piece', 'http://www.example.com']);

div.addEventListener('click', function () {
    _paq.push(['trackContentInteraction', 'Content Name', 'Content Piece', 'tabActivated']);
});
```

Be aware that each call to one of those two methods will send one request to your Piwik tracker instance. Calling those methods too many times can cause performance problems.

## Tracking Content Impressions API

Content impressions are logically not really events and I don't think it makes sense to use them here. It would also make it harder to analyze events when they are mixed with pieces of content.

* To track a content impression you will need to send the URL parameters `c_n`, `c_p` and `c_t` for name, piece and target along a tracking request.
* `c_p` for content piece and `c_t` for content target is optional.
* Multiple content impressions can be sent using bulk tracking for faster performance

## Tracking content interactions API
Contrary to impressions, clicks are actually events and it would be nice to use events here unless it is not an outlink or download to not lose such tracking data.

* To track a content interaction you will need to send at least the URL parameters `c_n`, `c_p` and `c_i` for name and interaction

We will link interactions to impressions at archiver time.

## Database

* New column `idaction_content_url` and `idaction_content_piece` in `log_link_visit_action`. For name `idaction_name` can be reused?

Could we also reuse `idaction_url` instead of adding new column `idaction_content_url`?
And we could also store the URL of the page showing the Content in `idaction_url_ref`. (reusing columns is good in this case)

  * Would we need a new column for each piece of content in action table to make archiver work? --> would result in many! columns
  * or would we need a new table for each piece of content to make archiver work? --> would be basically a copy of the link_action table and therefore not really makes sense I reckon. Only a lot of work. Logically I am not sure if an impression is actually an "action" so it could make sense
  * or would we store the pieces serialized as JSON in a `content` column? I don't know anything about the archiver but I think it wouldn't work at all
  * or would we create an action entry for each piece of content? --> yes I think! 

Yes it seems most logical to create an action entry for each Content.

## Thoughts on piwik.js
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
Nothing special here I think. We would probably automatically detect the type of content (image, video, text, sound, ...) depending on the content eg in case it ends with [.jpg, .png, .gif] it could be recognized as image content and show a banner in the report.

## TODO
* UI tests


## Notes:
* Referrer gets lost when using piwik.php
* Single page applications will always want to disable interactions as redirect would not fit into their concept!!!
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
* FYI: Piwik Mobile displays currently only one metric, so people won't see impressions and number of interactions or ratio next to each other
* If user wants to track only visible content we'll need to wait until the websites load (not DOMContentLoaded) event is triggered. Otherwise CSS might be not be applied yet and we cannot detect whether node is actually visible. Downside: Some websites might take > 10 seconds until this event is triggered. Depending on how good they are developed. During this time the user might be already no longer on that page or might have already scrolled to somewhere else.
* If user wants to track all content impressions (not only the visible ones) we'd probably have to wait until at least DOMContentLoaded event is triggered
* If the load event takes like 10 seconds later, the user has maybe already scrolled and seen some content blocks but we cannot detect... so considering viewport we need to assume all above the deepest scrollpoint was seen


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

13. Why do we track an event for an interaction? Which is with the currently implementation done only on a click to an internal URL anyway... does it actually make sense? I mean there will be pageview -> content + event action -> same pageview after redirect. We would track same information 3 times
It makes actually no sense and I will remove it again. It makes no sense because:
* We would currently only track links to the same website as an event (as only there piwik.php is used), we could use it for other links as well but why...
* A click to an internal page of the same website is simply no event per se. Also to an outlink or download... it is not an event
* As it is possible that we would add many different EventNames (= ContentNames) and EventActions (=ContentInteraction) it would maybe make it harder for some users to analyze their event names/actions that they use for other things
* The tracked content will be already displayed in the content report anyway, why displaying the same data in 2 reports (events and contents or actually even 3 reports as a pageview will be later tracked as well). There is no value in it
* ...
