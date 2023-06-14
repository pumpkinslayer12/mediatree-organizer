# Directory Viewer for Filebird

**Contributors:** pumpkinslayer12

**Tags:** media, directory, viewer

**Requires at least:** 5.0

**Tested up to:** 5.9

**Stable tag:** 1.0

**License:** GPLv3 or later

**License URI:** [https://www.gnu.org/licenses/gpl-3.0.html](https://www.gnu.org/licenses/gpl-3.0.html)


## Description

MediaTree Organizer enhances the WordPress Media Library by adding a dynamic directory tree for easy categorization and management of media files. It also offers a shortcode feature for displaying directories and files on the front-end.

## Installation

1. Upload the plugin files to the `/wp-content/plugins/mediatree-organizer` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.

## Usage

Use the shortcode `[mediatree-organizer folder_id=0]` to display the directory structure starting from the root directory. You can change the `folder_id` to display another directory.

To get a specific folder id, inspect the output directory structure using console. Each directory will have the attribute id="folder-x", where x is the folder id.

## Frequently Asked Questions


**What is the shortcode to display the directory structure?**

The shortcode is `[mediatree-organizer folder_id=0]`. You can change the `folder_id` to display another directory.

**How many shortcodes can be used on one page?**

Currently only one shortcode can be used, per page. This limitation will be addressed in subsequent releases.

## Changelog

**1.0**

- Initial release.

## Upgrade Notice

**1.0**

- Initial release.