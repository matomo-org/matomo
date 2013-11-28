// Part of OpenPhantomScripts
// http://github.com/mark-rushakoff/OpenPhantomScripts

// Copyright (c) 2012 Mark Rushakoff

// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to
// deal in the Software without restriction, including without limitation the
// rights to use, copy, modify, merge, publish, distribute, sublicense, and/or
// sell copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:

// The above copyright notice and this permission notice shall be included in
// all copies or substantial portions of the Software.

// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
// FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
// IN THE SOFTWARE

var fs  = require("fs");
var url = 'http://localhost/tests/javascript';

function printError(message) {
    fs.write("/dev/stderr", message + "\n", "w");
}

var page = require("webpage").create();

function isPhantomAttached() {
    return page.evaluate(function() {return window.phantomAttached})
}

page.onResourceReceived = function() {
    page.evaluate(function() {
        if (!window.QUnit || window.phantomAttached) return;

        QUnit.config.done.push(function(obj) {
            console.log("Tests passed: " + obj.passed);
            console.log("Tests failed: " + obj.failed);
            console.log("Total tests:  " + obj.total);
            console.log("Runtime (ms): " + obj.runtime);
            window.phantomComplete = true;
            window.phantomResults = obj;
        });

        QUnit.log(function(obj) {
            if (!obj.result) {
                var errorMessage = "Test failed in module " + obj.module + ": '" + obj.name + "' \nError: " + obj.message;

                if (obj.actual) {
                    errorMessage += " \nActual: " + obj.actual;
                }

                if (obj.expected) {
                    errorMessage += " \nExpected: " + obj.expected;
                }

                errorMessage += " \nSource: " + obj.source + "\n\n";

                console.log(errorMessage);
            }
        });

        window.phantomAttached = true;
    });
}

page.onConsoleMessage = function(message) {
    console.log(message);
}

page.onAlert = function(msg) {
    console.log('ALERT: ' + msg + "\n");
}

page.open(url, function(success) {
    if (success === "success") {
        if (!isPhantomAttached()) {
            printError("Phantom callbacks not attached in time.  See http://github.com/mark-rushakoff/OpenPhantomScripts/issues/1");
            phantom.exit(1);
        }

        setInterval(function() {
            if (page.evaluate(function() {return window.phantomComplete;})) {
                var failures = page.evaluate(function() {return window.phantomResults.failed;});
                phantom.exit(failures);
            }
        }, 250);
    } else {
        printError("Failure opening " + url);
        phantom.exit(1);
    }
});