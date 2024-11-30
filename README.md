# Metadata Import/Export for Yoast

**Metadata Import/Export for Yoast** is a WordPress plugin that enables you to seamlessly import and export Yoast SEO metadata using CSV files. 

It's perfect for bulk editing SEO metadata or migrating SEO settings between WordPress® sites.

### Features

- **Import Yoast SEO Metadata**: Bulk import SEO metadata from a CSV file.
- **Export Yoast SEO Metadata**: Export your site's SEO metadata to a CSV file for backup or bulk editing.
- **Custom Post Type Support**: Choose which post types to include in import/export operations.
- **User Role Access Control**: Define which user roles have access to the plugin's functionalities.
- **Settings Page**: Configure plugin settings according to your needs.
- **Progress Indicators**: Visual progress bars during import and export processes.
- **Detailed Statistics**: View comprehensive stats after import/export operations.

## Installation

### From the WordPress® Dashboard

1. **Download the Plugin**: Get the latest version from the [GitHub repository](https://github.com/robertdevore/metadata-import-export-for-yoast/).
2. **Navigate to Plugins**: Go to `Plugins` > `Add New`.
3. **Upload Plugin**: Click on `Upload Plugin` and select the downloaded ZIP file.
4. **Install and Activate**: Click `Install Now`, then `Activate`.

### Manual Installation

1. **Download and Unzip**: Download the plugin ZIP file and unzip it.
2. **Upload to Server**: Upload the `metadata-import-export-for-yoast` folder to the `/wp-content/plugins/`directory.
3. **Activate Plugin**: Activate the plugin from the `Plugins` menu in WordPress.

## Usage

### Accessing the Plugin

After activation, the plugin adds a new submenu under the Yoast SEO menu:

- Navigate to `SEO` > `Import/Export`.

### Importing Metadata

1. **Navigate to Import Tab**: Go to the `Import` tab within the plugin page.
2. **Upload CSV File**: Click `Choose File`, select your CSV file, and ensure it follows the required format.
3. **Start Import**: Click `Upload and Import`.
4. **Monitor Progress**: A progress bar will display the import status.
5. **Review Results**: Upon completion, view the import statistics and any error messages.

### Exporting Metadata

1. **Navigate to Export Tab**: Go to the `Export` tab.
2. **Start Export**: Click on `Export Metadata`.
3. **Monitor Progress**: A progress bar will display the export status.
4. **Download CSV File**: Upon completion, a download link for the CSV file will be provided.

### Configuring Settings

1. **Navigate to Settings Tab**: Go to the `Settings` tab.
2. **Select Post Types**: Choose which post types to include in import/export operations.
3. **Select User Roles**: Define which user roles can access the plugin.
4. **Save Changes**: Click `Save Changes` to apply your settings.

## CSV File Format

Your CSV file should have the following headers in this exact order:

- **ID**: The unique ID of the post.
- **Post Type**: The type of post (e.g., `post`, `page`).
- **Slug**: The slug of the post.
- **Keyphrase**: The focus keyphrase for SEO.
- **SEO Title**: The SEO title of the post.
- **SEO Description**: The meta description for SEO.

### Example CSV Content
    ```
    ID,Post Type,Slug,Keyphrase,SEO Title,SEO Description
    1,post,hello-world,"Hello World","Welcome to My Blog","This is the first post on my blog."
    2,page,about-us,"About Us","Learn About Us","Find out more about our company."
    ```

## Permissions

By default, only administrators have access to the plugin. You can extend access to other user roles via the **Settings** tab.

## Frequently Asked Questions

### Does this plugin support custom post types?

Yes, it supports any public custom post types. You can select which ones to include in the **Settings** tab.

### Can I schedule imports or exports?

Currently, the plugin does not support scheduled operations. Imports and exports are initiated manually.

### Is there a limit to the number of posts I can import or export?

While there is no set limit, very large datasets may lead to performance issues. It's recommended to work with datasets that your server can handle efficiently.

### What happens if the CSV file has incorrect data?

The plugin will attempt to import valid data and skip invalid entries. Error messages will be displayed after the import process.

## Contributing

Contributions are welcome! Feel free to submit issues or pull requests 🤘

## License

This plugin is licensed under the [GNU General Public License v2.0 or later](http://www.gnu.org/licenses/gpl-2.0.txt).