# Client Access

This plugin was made for a specific client use-case and is *not recommended for general use* (unless you want to edit the code for your own needs!).

The plugin does the following things:
- Adds two new user roles ('Client' and 'Site Manager').
- Greatly restricts admin functionality for these roles, eliminating things like the Dashboard, page metaboxes, user profile options, list table columns, TinyMCE defaults, and more.
- Adds a 'Documents' custom field to Pages, where a user with these roles can upload specific documents to that page.
- Adds the optional ability to allow 'Site Managers' to add and manage Subscriber users (and a settings page to enable/disable this feature).
- Adds custom handling of other plugins, including Tablepress, AppPresser, and MonsterInsights.

*Note:* ACF Pro was bundled with this plugin, but due to licensing cannot be added here. You can add the ACF plugin files under `/includes/acf/` to ge tthings working, or change the ACF path and directory using appropriate filters.

*Note:* This plugin has not been made compatible with the Gutenberg editor and is recommended for use with the Classic Editor plugin.
