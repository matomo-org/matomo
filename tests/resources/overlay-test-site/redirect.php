<?php

// Redirects to the test site's root page the way many sites do (e.g. to add a trailing slash), with a
// Referrer-Policy on the redirect itself. The browser then trims the referrer of the redirected request
// to its origin, so the tracker cannot detect the Overlay session from document.referrer. Real sites
// usually send strict-origin-when-cross-origin; 'origin' trims same-origin requests too, so this also
// holds when the test site and Matomo share a host.
header('Referrer-Policy: origin');
header('Location: ./', true, 302);
