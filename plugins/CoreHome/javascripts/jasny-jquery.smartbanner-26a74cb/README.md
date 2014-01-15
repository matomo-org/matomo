jQuery Smart Banner
===================

[Smart Banners][1] are a new feature in iOS 6 to promote apps on the App Store from a website. This jQuery plugin
brings this feature to older iOS versions, Android devices and for Windows Store apps.

## Usage ##
    <html>
      <head>
        <title>YouTube</title>
        <meta name="author" content="Google, Inc.">
        <meta name="apple-itunes-app" content="app-id=544007664">
        <meta name="google-play-app" content="app-id=com.google.android.youtube">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" href="jquery.smartbanner.css" type="text/css" media="screen">
        <link rel="apple-touch-icon" href="apple-touch-icon.png">
      </head>
      <body>
        ...
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8/jquery.min.js"></script>
        <script src="jquery.smartbanner.js"></script>
        <script type="text/javascript">
          $(function() { $.smartbanner() } )
        </script>
      </body>
    </html>

## Options ##
    $.smartbanner({
      title: null, // What the title of the app should be in the banner (defaults to <title>)
      author: null, // What the author of the app should be in the banner (defaults to <meta name="author"> or hostname)
      price: 'FREE', // Price of the app
      appStoreLanguage: 'us', // Language code for App Store
      inAppStore: 'On the App Store', // Text of price for iOS
      inGooglePlay: 'In Google Play', // Text of price for Android
	  inWindowsStore: 'In the Windows Store', // Text of price for Windows
      icon: null, // The URL of the icon (defaults to <meta name="apple-touch-icon">)
      iconGloss: null, // Force gloss effect for iOS even for precomposed
      button: 'VIEW', // Text for the install button
      scale: 'auto', // Scale based on viewport size (set to 1 to disable)
      speedIn: 300, // Show animation speed of the banner
      speedOut: 400, // Close animation speed of the banner
      daysHidden: 15, // Duration to hide the banner after being closed (0 = always show banner)
      daysReminder: 90, // Duration to hide the banner after "VIEW" is clicked *separate from when the close button is clicked* (0 = always show banner)
      force: null // Choose 'ios', 'android' or 'windows'. Don't do a browser check, just always show this banner
    })

  [1]: http://developer.apple.com/library/ios/#documentation/AppleApplications/Reference/SafariWebContent/PromotingAppswithAppBanners/PromotingAppswithAppBanners.html
