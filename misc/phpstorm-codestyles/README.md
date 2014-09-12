Phpstorm has an awesome feature called "Reformat code" which reformats all PHP code to follow a particular selected coding style.

Piwik uses PSR coding standard for php source code. We use a slightly customized PSR style
(because the default PSR style in Phpstorm results in some unwanted changes).

Steps:
 * Use latest Phpstorm
 * Copy this Piwik_codestyle.xml file in your  `~/.WebIde80/config/codestyles/`
  * If you use Windows or Mac see which path to copy at: http://intellij-support.jetbrains.com/entries/23358108
  * To automatically link to the file in Piwik:
  `$ ln -s ~/dev/piwik-master/misc/phpstorm-codestyles/Piwik_codestyle.xml  ~/.WebIde80/config/codestyles/Piwik_codestyle.xml`

 * Restart PhpStorm
 * Select this coding in Settings > Code style.

Phpstorm can also be configured to apply the style automatically before commit.

You are now writing code that respects Piwik coding standards. Enjoy!

Reference: http://piwik.org/participate/coding-standards/

