# build jqplot-custom.min.js
# the yuicompressor needs to be set up in piwik/js (see piwik/js/README.md)

find . -name 'jqplot.*.js' -print|xargs cat > jqplot-custom.min.js-temp

java -jar ../../js/yuicompressor-2.4.2/build/yuicompressor-2.4.2.jar --type js --line-break 1000 jqplot-custom.min.js-temp > jqplot-custom.min.js 

rm ./jqplot-custom.min.js-temp
