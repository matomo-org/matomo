{
    "app": {
        "package": "piwik",
        "name": "Piwik",
        "version": "{{VERSION}}",
        "section": "Web Hosting",
        "visibility": true,
        "priority": "optional",
        "depends": [],
        "conflicts": [],
        "suggests": [],
        "maintainer": "Piwik",
        "email": "hello@piwik.org",
        "website": "http://piwik.org/",
        "architecture": "x86-64",
        "firmware": "0.6.0",
        "description": "Open source, self-hosted web analytics.",
        "changes": "http://piwik.org/changelog/",
        "tags": ["analytics", "visits", "visitors", "hits"]
    },
    "install": {
        "dep-service": {
            "start": ["httpd", "mysql"],
            "restart": []
        }
    }
}
