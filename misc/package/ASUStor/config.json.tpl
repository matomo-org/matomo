{
    "app":{
        "package":"piwik",
        "name":"Piwik",
        "version":"{{VERSION}}",
        "section":"Web Hosting",
        "visibility":true,
        "priority":"optional",
        "depends":[],
        "conflicts":[],
        "suggests":[],
        "maintainer":"Piwik",
        "email":"hello@piwik.org",
        "website":"http://piwik.org/",
        "architecture":"any",
        "firmware":"any",
        "description":"Open source, self-hosted web analytics.",
        "changes":"http://piwik.org/changelog/",
        "tags":["analytics", "visits", "visitors", "hits"]
    },
    "desktop":{
        "icon":{
            "type":"webserver",
            "title":"Piwik"
        },
        "privilege":{
            "accessible":"users",
            "customizable":true
        }
    },
    "install":{
        "dep-service":{
            "start":["httpd", "mysql"],
            "restart":[]
        }
    }
}
