MVC specification
=================

Modules that need to display HTML 
- Installation
- Administration  modules
- All the reports modules

These modules can be part of the "core" or "plugins"

It must be *easy* for a plugin to display some data in the interface
Either 
- add a menu in the main menu, or a submenu
- Modify the footer/header
- Add a table in a page

A plugin can have a tab for its configuration

But at the same time a plugin can require to edit the template 
and add some dirty PHP code in the source so that the author doesn't
have to bother about model, view and controller if he wants to do something simple


* Using Smarty for template engine


Use cases
------------------------------------------------------------------------------------
1 - Plugin publishes the list of the last N visitors with their information 
2 - Display a Google map of the last visitors locations
3 - Display a DataTable of the last N new search keywords
4 - Feed tracking via feedburner API integration
5 - Alexa, pagerank, etc. summary in the dashboard
6 - Database import / export tool
7 - Plugin that add a selection for every user
------------------------------------------------------------------------------------

