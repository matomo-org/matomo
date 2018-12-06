Google Authenticator PHP class
==============================

* Copyright (c) 2012-2016, [http://www.phpgangsta.de](http://www.phpgangsta.de)
* Author: Michael Kliewe, [@PHPGangsta](http://twitter.com/PHPGangsta) and [contributors](https://github.com/PHPGangsta/GoogleAuthenticator/graphs/contributors)
* Licensed under the BSD License.

[![Build Status](https://travis-ci.org/PHPGangsta/GoogleAuthenticator.png?branch=master)](https://travis-ci.org/PHPGangsta/GoogleAuthenticator)

This PHP class can be used to interact with the Google Authenticator mobile app for 2-factor-authentication. This class
can generate secrets, generate codes, validate codes and present a QR-Code for scanning the secret. It implements TOTP
according to [RFC6238](https://tools.ietf.org/html/rfc6238)

For a secure installation you have to make sure that used codes cannot be reused (replay-attack). You also need to
limit the number of verifications, to fight against brute-force attacks. For example you could limit the amount of
verifications to 10 tries within 10 minutes for one IP address (or IPv6 block). It depends on your environment.

Usage:
------

See following example:

```php
<?php
require_once 'PHPGangsta/GoogleAuthenticator.php';

$ga = new PHPGangsta_GoogleAuthenticator();
$secret = $ga->createSecret();
echo "Secret is: ".$secret."\n\n";

$qrCodeUrl = $ga->getQRCodeGoogleUrl('Blog', $secret);
echo "Google Charts URL for the QR-Code: ".$qrCodeUrl."\n\n";

$oneCode = $ga->getCode($secret);
echo "Checking Code '$oneCode' and Secret '$secret':\n";

$checkResult = $ga->verifyCode($secret, $oneCode, 2);    // 2 = 2*30sec clock tolerance
if ($checkResult) {
    echo 'OK';
} else {
    echo 'FAILED';
}
```
Running the script provides the following output:
```
Secret is: OQB6ZZGYHCPSX4AK

Google Charts URL for the QR-Code: https://www.google.com/chart?chs=200x200&chld=M|0&cht=qr&chl=otpauth://totp/infoATphpgangsta.de%3Fsecret%3DOQB6ZZGYHCPSX4AK

Checking Code '848634' and Secret 'OQB6ZZGYHCPSX4AK':
OK
```

Installation:
-------------

- Use [Composer](https://getcomposer.org/doc/01-basic-usage.md) to
  install the package

- From project root directory execute following

```composer install```

- [Composer](https://getcomposer.org/doc/01-basic-usage.md) will take care of autoloading
  the library. Just include the following at the top of your file

  `require_once __DIR__ . '/../vendor/autoload.php';`

Run Tests:
----------

- All tests are inside `tests` folder.
- Execute `composer install` and then run the tests from project root
  directory
- Run as `phpunit tests` from the project root directory


ToDo:
-----
- ??? What do you need?

Notes:
------

If you like this script or have some features to add: contact me, visit my blog, fork this project, send pull requests, you know how it works.