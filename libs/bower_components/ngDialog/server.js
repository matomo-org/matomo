var express = require('express');
var http = require('http');
var app = express();

app.use(express.static( __dirname ));

var port = process.env.PORT || 3000;
http.createServer(app).listen(port);
console.log('Server running on ' + port);
