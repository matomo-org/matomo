# jQuery.ScrollTo

### Installation and usage

Using [bower](https://github.com/twitter/bower):
```bash
bower install jquery.scrollTo
```
Using npm:
```bash
npm install jquery.scrollto
```
Using [composer](http://getcomposer.org/download/):

Either run

```
php composer.phar require --prefer-dist flesler/jquery.scrollto "*"
```

or add

```
"flesler/jquery.scrollto": "*"
```

to the require section of your composer.json.

### Downloading Manually

If you want the latest stable version, get the latest release from the [releases page](https://github.com/flesler/jquery.scrollTo/releases).

### Notes

* Apart from the target and duration, the plugin can receive a hash of settings. Documentation and examples are included in the source file.

* If you are interested in animated "same-page-scrolling" using anchors(href="#some_id"), check http://github.com/flesler/jquery.localScroll

* For a slideshow-like behavior using scrolling, check http://github.com/flesler/jquery.serialScroll

* The target can be specified as:
  * A Number/String specifying a position using px or just the number.
  * A string selector that will be relative, to the element that is going to be scrolled, and must match at least one child.
  * A DOM element, logically child of the element to scroll.
  * A hash { top:x, left:y }, x and y can be any kind of number/string like described above.

* The plugin supports relative animations

* 'em' and '%' are not supported as part of the target, because they won't work with jQuery.fn.animate.
  
* The plugin might fail to scroll an element, to an inner node that is nested in more scrollable elements. This seems like an odd situation anyway.

* Both axes ( x, y -> left, top ) can be scrolled, you can send 'x', 'y', 'xy' or 'yx' as 'axis' inside the settings.

* If 2 axis are scrolled, there's an option to queue the animations, so that the second will start once the first ended ('xy' and 'yx' will have different effects)

* The option 'margin' can be set to true, then the margin of the target element, will be taken into account and will be deducted.

* 'margin' will only be valid, if the target is a selector, a DOM element, or a jQuery Object.

* The option 'offset' allows to scroll less or more than the actual target by a defined amount of pixels. Can be a number(both axes), { top:x, left:y } or a function that returns an object with top & left.

* The option 'over' lets you add or deduct a fraction of the element's height and width from the final position. so over:0.5 will scroll to the middle of the object. can be specified with {top:x, left:y}

* Don't forget the callback event is now called 'onAfter', and if queuing is activated, then 'onAfterFirst' can be used.

* If the first axis to be scrolled, is already positioned, that animation will be skipped, to avoid a delay in the animation.

* The call to the plugin can be made in 2 different ways: $(...).scrollTo( target, duration, settings ) or $(...).scrollTo( target, settings ). Where one of the settings is 'duration'.

* If you find any bug, or you have any advice, don't hesitate to open an issue. 
