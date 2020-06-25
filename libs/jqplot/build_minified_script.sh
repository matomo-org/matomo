# build jqplot-custom.min.js
# the yuicompressor needs to be set up in piwik/js (see piwik/js/README.md)

cat jqplot.core.js > jqplot-custom.min.js-temp 
cat jqplot.linearAxisRenderer.js >> jqplot-custom.min.js-temp
cat jqplot.axisTickRenderer.js >> jqplot-custom.min.js-temp
cat jqplot.axisLabelRenderer.js >> jqplot-custom.min.js-temp
cat jqplot.tableLegendRenderer.js >> jqplot-custom.min.js-temp
cat jqplot.lineRenderer.js >> jqplot-custom.min.js-temp
cat jqplot.linePattern.js >> jqplot-custom.min.js-temp
cat jqplot.markerRenderer.js >> jqplot-custom.min.js-temp
cat jqplot.divTitleRenderer.js >> jqplot-custom.min.js-temp
cat jqplot.canvasGridRenderer.js >> jqplot-custom.min.js-temp
cat jqplot.shadowRenderer.js >> jqplot-custom.min.js-temp
cat jqplot.shapeRenderer.js >> jqplot-custom.min.js-temp
cat jqplot.sprintf.js >> jqplot-custom.min.js-temp
cat jqplot.themeEngine.js >> jqplot-custom.min.js-temp
cat plugins/jqplot.pieRenderer.js >> jqplot-custom.min.js-temp
cat plugins/jqplot.barRenderer.js >> jqplot-custom.min.js-temp
cat plugins/jqplot.categoryAxisRenderer.js >> jqplot-custom.min.js-temp
cat plugins/jqplot.canvasTextRenderer.js >> jqplot-custom.min.js-temp
cat plugins/jqplot.canvasAxisTickRenderer.js >> jqplot-custom.min.js-temp

java -jar ../../js/yuicompressor-2.4.2/build/yuicompressor-2.4.2.jar --type js --line-break 1000 jqplot-custom.min.js-temp > jqplot-custom.min.js 

rm ./jqplot-custom.min.js-temp
