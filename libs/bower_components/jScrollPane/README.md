#jScrollPane - cross browser custom scrollbars

jScrollPane is a [jQuery](http://www.jquery.com/) plugin which allows you to replace a browser's default scrollbars (on an element which has **overflow: auto**) with a HTML structure that can be easily skinned using CSS.

To see a bunch of examples of jScrollPane in action please visit the [jScrollPane website](http://jscrollpane.kelvinluck.com/). All of the code for the website is available from this repository so please feel free to download and use it!

##Contributing

There is a simple [grunt](http://gruntjs.com) based build script which will help to produce a minified version of
jScrollPane if you make any modifications and want to submit a pull request. You can find it in the `build/` directory.

To use it first make sure you have [node](http://nodejs.org/), npm and the `grunt-cli` module installed:

`npm install -g grunt-cli`

Then:

```
cd build
grunt
```

Please remember to update the changelog in the comment at the header of both JS files when submitting a pull request.