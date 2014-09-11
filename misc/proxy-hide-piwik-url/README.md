## Piwik Proxy Hide URL
This script allows to track statistics using Piwik, without revealing the
Piwik Server URL. This is useful for users who track multiple websites
on the same Piwik server, but don't want to show the Piwik server URL in
the source code of all tracked websites.

### Requirements
To run this properly you will need

 * Piwik server latest version
 * One or several website(s) to track with this Piwik server, for example http://trackedsite.com
 * The website to track must run on a server with PHP5 support
 * In your php.ini you must check that the following is set: `allow_url_fopen = On`

### How to track trackedsite.com in your Piwik without revealing the Piwik server URL?

1. In your Piwik server, login as Super user
2. create a user, set the login for example: "UserTrackingAPI"
3. Assign this user "admin" permission on all websites you wish to track without showing the Piwik URL
4. Copy the "token_auth" for this user, and paste it below in this file, in `$TOKEN_AUTH = "xyz"`
5. In this file, below this help test, edit $PIWIK_URL variable and change http://your-piwik-domain.example.org/piwik/ with the URL to your Piwik server.
6. Upload this modified piwik.php file in the website root directory, for example at: http://trackedsite.com/piwik.php
   This file (http://trackedsite.com/piwik.php) will be called by the Piwik Javascript,
   instead of calling directly the (secret) Piwik Server URL (http://your-piwik-domain.example.org/piwik/).
7. You now need to add the modified Piwik Javascript Code to the footer of your pages at http://trackedsite.com/
   Go to Piwik > Settings > Websites > Show Javascript Tracking Code.
   Copy the Javascript snippet. Then, edit this code and change the last lines to the following:

   ```
   [...]
   (function() {
       var u="//trackedsite.com/";
       _paq.push(["setTrackerUrl", u+"piwik.php"]);
       _paq.push(["setSiteId", "trackedsite-id"]);
       var d=document, g=d.createElement("script"), s=d.getElementsByTagName("script")[0];
       g.type="text/javascript"; g.async=true; g.defer=true; g.src=u+"piwik.php"; s.parentNode.insertBefore(g,s);
   })();
   </script>
   <!-- End Piwik Code -->
   ```

   What's changed in this code snippet compared to the normal Piwik code?

   * the (secret) Piwik URL is now replaced by your website URL
   * the "piwik.js" becomes "piwik.php" because this piwik.php proxy script will also display and proxy the Javascript file
   * the `<noscript>` part of the code at the end is removed,
     since it is not currently used by Piwik, and it contains the (secret) Piwik URL which you want to hide.
   * make sure to replace trackedsite-id with your idsite again.

 8. Paste the modified Piwik Javascript code in your website "trackedsite.com" pages you wish to track.
    This modified Javascript Code will then track visits/pages/conversions by calling trackedsite.com/piwik.php
    which will then automatically call your (hidden) Piwik Server URL.
 9. Done!
    At this stage, example.com should be tracked by your Piwik without showing the Piwik server URL.
    Repeat the steps 6, 7 and 8 for each website you wish to track in Piwik.
