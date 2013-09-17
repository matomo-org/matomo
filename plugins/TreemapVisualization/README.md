# Description

TreemapVisualization is a Piwik plugin that provides the treemap report visualization.

# Features

The treemap visualization will show rows of data tables as squares whose size correspond to the metric of the row. For example, if you're looking at visits, the row with the most visits will take up the most space. Just like the other graph visualizations, you can use it to easily see which rows have the largest values. The treemap differs from other graphs though, in that it shows much more rows.

The treemap visualization will also show you one thing that no other visualization included with Piwik does: the evolution of each row. You can hover over a treemap square to see how much the row changed from the last period. Each treemap square is also colored based on the evolution. A red square means the change is negative; a green square means the change is positive. The more green the bigger the change; the more red the smaller the change.

# Limitations

Currently there is one known limitation:

* Treemaps will not work with flattened tables. Currently, if a table is flattened, the treemap icon will be removed.
