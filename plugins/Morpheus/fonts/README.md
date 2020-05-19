To change / add / remove icons go to https://icomoon.io/app/ and upload `selection.json`.
This will allow you to customize the font. When using third party icons, don't forget to mention
them in `LEGALNOTICE`.

## WOFF2

WOFF2 is a font format that allows higher compression than WOFF and is already supported by [all major browsers](https://caniuse.com/#feat=woff2).

Unfortunately icomoon doesn't create WOFF2 files, but as WOFF2 is just a container for ttf fonts, they can be easily converted.

### Steps

- get `woff2_compress`
    - e.g. via the `woff2` debian package or similar packages for other distributions
    - or compile it yourself as explained on https://github.com/google/woff2
- convert all .ttf 
 files
    - `woff2_compress matomo.ttf`
