/*
*  Proof of concept of a testserver for CasperJS.
*/

/*global CasperError exports phantom require*/

var fs = require('fs');
var webserver = require('webserver');

/**
 * Creates a server instance.
 *
 * @param  Casper  casper   A Casper instance
 * @param  Object  options  Server options
 * @return Server
 */
exports.create = function create(casper, options) {
    "use strict";
    var server = new Server(casper, options);
    casper.server = server;
    return server;
};

/**
 * Casper server: serve responses, fake them, listen to requests
 *
 * @param  Casper       casper   A valid Casper instance
 * @param  Object|null  options  Options object
 */
var Server = function Server(casper, options) {
    "use strict";
    
    this.casper = casper;

    options = options || {};

    this.options = {};
    this.options.port = options.port || 8008; // port the server will listen to
    this.options.defaultStatusCode = options.defaultStatusCode || 200; // can be overrided through options
    this.options.responsesDir = options.responsesDir || './';   // where to look for response content files

    this.watchedPaths = {
        "^/(\\?.*)?$": {filePath: 'test/index.html', permanent: true},
        "/src/jquery.browser.js": {filePath: 'test/src/jquery.browser.js', permanent: true},
        "/src/jquery-1.10.2.min.js": {filePath: 'test/src/jquery-1.10.2.min.js', permanent: true},
    };

    this.watchedRequests = {};

};
exports.Server = Server;

/**
 * Start the server
 */
Server.prototype.start = function start() {
    "use strict";
    this.webserver = webserver.create();
    var self = this;
    this.service = this.webserver.listen(this.options.port, function (request, response) {self._serve(request, response);});
    this.log("server started");
};

/**
 * Stop the server
 */
Server.prototype.end = function start() {
    "use strict";
    this.webserver.close();
    this.log("server closed");
};

/**
 * Internal method, callback of the webserver.listen
 *
 * See: https://github.com/ariya/phantomjs/wiki/API-Reference#wiki-webserver-module
 */

Server.prototype._serve = function serve(request, response) {
    "use strict";
    response.statusCode = this.options.defaultStatusCode;
    this.log("handling " + request.method + " on " + request.url, "debug");

    // Handle request
    if (typeof this.watchedRequests[request.url] !== "undefined") {
        if (request.headers['Content-Type'] != "application/x-www-form-urlencoded") {
            // phantomJS webserver does not handle multipart form, which
            // is the default content-type of HTML5 FormData
            this._splitFormData(request);
        }
        this.watchedRequests[request.url] = request;
    }

    // Handle response
    var options = {};
    for (var path in this.watchedPaths) {
        if (request.url.search(path) !== -1) {
            options = this.watchedPaths[path];
            this.log("Build response from watched path " + request.url);
            if (!options.permanent) {
                // Automatically unwatch path, not to pollute tests context
                this.unwatchPath(path);
            }
            break;
        }
    }
    options._request = request;
    this._buildResponse(response, options);
};


/*
 * Modify the response according to options
 *
 * @param    Object    response    the PhantomJS.webserver response instance
 * @param    Object    options     the options to use (content, filePath...)
 *
 */
Server.prototype._buildResponse = function _buildResponse(response, options) {

    var content,
        statusCode,
        filePath,
        options = options || {};

    if (options.content) {
        if (typeof options.content === 'function') {
            content = options.content(options._request);
        }
        else {
            content = options.content;
        }
    }
    else {
        if (options.filePath) {
            if (typeof options.filePath === 'function') {
                filePath = options.filePath(options._request);
            }
            else {
                filePath = options.filePath;
            }
        }
        else {
            filePath = options._request.url;
        }
        if (!/^(\.|\/)/.test(filePath)) {
            filePath = this.options.responsesDir + filePath;
        }
        if (fs.exists(filePath)) {
            this.log("Getting content from " + filePath);
            content = fs.read(filePath);
        }
        else {
            this.log("File not found: " + filePath, 'error');
        }
    }
    if (options.statusCode) {
        response.statusCode = options.statusCode;
    }
    if (!content) {
        response.statusCode = 404;
        content = 'No handler for the url ' + options._request.url;
    }
    response.write(content);
    response.close();
};

/*
 *  Shortcut for logging with a prefix
 */
Server.prototype.log = function log(msg, level) {
    if (typeof level == "undefined") {
        level = "debug";
    }
    this.casper.log("[casperserver] " + msg, level);
};

/*
 * replace the unparsed post string with a readable key/value
 * EXPERIMENTAL!
 * TODO: move to phantomJS or Mongoose?
 */
Server.prototype._splitFormData = function _splitFormData(request) {
    // Example of raw post:
    // "------WebKitFormBoundarysxecJyeWZZikD2xz\r\nContent-Disposition: form-data; name=\"name\"\r\n\r\nBirds map\r\n------WebKitFormBoundarysxecJyeWZZikD2xz\r\nContent-Disposition: form-data; name=\"description\"\r\n\r\nWhere have you seen birds?\r\n------WebKitFormBoundarysxecJyeWZZikD2xz\r\nContent-Disposition: form-data; name=\"licence\"\r\n\r\n1\r\n------WebKitFormBoundarysxecJyeWZZikD2xz\r\nContent-Disposition: form-data; name=\"center\"\r\n\r\nPOINT (15.9191894531249982 49.0018439179785261)\r\n------WebKitFormBoundarysxecJyeWZZikD2xz--\r\n"
    var post = {},
        rawPost = request.post.trim(),
        boundary = "--" + request.headers['Content-Type'].split('boundary=')[1],
        els = rawPost.split(boundary),
        subels, name, value;
    for (var j = 1, k = els.length; j < k; j++) {
        if (!els[j] || els[j] == "--") {
            continue;
        }
        subels = els[j].split('\r\n');
        value = subels[3];
        name = subels[1].match(/name="(\S+)"/i)[1];
        post[name] = value;
    }
    request.post = post;
    return request;
};

/**
 * Indicate how to serve the response for some specific path
 *
 * @param  Regex   path     the path to watch
 * @param  Object  options  options to use for response build
 */
Server.prototype.watchPath = function(path, options) {
    this.watchedPaths[path] = options;
};

/**
 * Unwatch a path
 *
 * @param  Regex   path     the path to unwatch
 */
Server.prototype.unwatchPath = function(path) {
    delete this.watchedPaths[path];
};

/**
 * Indicate that the request for the specific path has to be stored for
 * later inspection
 *
 * @param  String   path     the path to watch
 */
Server.prototype.watchRequest = function(path) {
    // Watch a response to be able to test its value after process
    this.watchedRequests[path] = null;
};

/**
 * Stop watching some path
 *
 * @param  String   path     the path to watch
 */
Server.prototype.unwatchRequest = function(path) {
    delete this.watchedRequests[path];
};

/**
 * Indicate how to serve the response for some specific path
 *
 * @param  String   path     the path to watch
 * @return Object  request  a request instance
 */
Server.prototype.getWatchedRequest = function (path) {
    if (!path) {
        this.log("Can't retrieve watchedRequest for null path");
        return;
    }
    var request = this.watchedRequests[path];
    delete this.watchedRequests[path];
    return request;
};