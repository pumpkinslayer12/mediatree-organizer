# MediaTree Organizer

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

# MediaTree Organizer Improvement Plan

This document outlines a focused improvement plan for the MediaTree Organizer WordPress plugin, prioritizing essential enhancements for a solid MVP.

## 1. Code Organization and Structure

- [ ] **Consistent Function Prefix Usage**
  - [ ] Audit all functions to ensure `mto_` prefix is consistently applied
  - [ ] Standardize hook naming with `mto_` prefix

- [ ] **File Structure Optimization**
  - [ ] Separate core functionality into logical components (taxonomy, AJAX handlers, rendering)
  - [ ] Move helper functions to a dedicated `/includes/helpers.php` file
  - [ ] Ensure all includes follow WordPress loading best practices

- [ ] **Function Organization**
  - [ ] Group related functions together within files
  - [ ] Add function section headers as comments
  - [ ] Implement logical function ordering (initialization first, then hooks, then implementation)
  - [ ] Review for duplicate or near-duplicate functions that can be consolidated

## 2. Code Quality and Standards

- [ ] **WordPress Coding Standards**
  - [ ] Add appropriate file headers with version, author, and license information

- [ ] **Documentation**
  - [ ] Add PHPDoc blocks to all functions with @param, @return, and @since tags
  - [ ] Create inline documentation for complex code sections

- [ ] **Naming Conventions**
  - [ ] Review all variable names for clarity and consistency
  - [ ] Ensure function names clearly describe their purpose

- [ ] **Code Duplication**
  - [ ] Identify and eliminate repeated code patterns
  - [ ] Extract common functionality into helper functions
  - [ ] Create utility functions for frequently used operations

## 3. Security Enhancements

- [ ] **Input Validation and Sanitization**
  - [ ] Audit all user input handling
  - [ ] Implement strict type checking for function parameters
  - [ ] Use appropriate WordPress sanitization functions consistently:
    - [ ] `sanitize_text_field()` for general text
    - [ ] `absint()` for integer values
    - [ ] `sanitize_title()` for slugs
    - [ ] `wp_kses_post()` for rich text

- [ ] **Output Escaping**
  - [ ] Review all echo statements and ensure proper escaping:
    - [ ] `esc_html()` for regular text
    - [ ] `esc_attr()` for HTML attributes
    - [ ] `esc_url()` for URLs
    - [ ] `wp_kses()` for allowing specific HTML

- [ ] **AJAX Security**
  - [ ] Verify all AJAX endpoints have nonce checks
  - [ ] Implement capability checks before performing actions
  - [ ] Log failed security checks to WordPress error log

- [ ] **Database Interaction**
  - [ ] Use prepared statements for all database queries
  - [ ] Implement proper error handling for database operations
  - [ ] Validate taxonomy terms before creation/modification

## 4. Performance Optimization

- [ ] **Asset Loading**
  - [ ] Audit script and style dependencies
  - [ ] Implement conditional loading of assets (only when needed)
  - [ ] Add version numbers to asset URLs based on file modification time

- [ ] **Database Optimization**
  - [ ] Implement caching for frequently accessed data
  - [ ] Optimize taxonomy queries for better performance
  - [ ] Use transients for storing complex query results

- [ ] **JavaScript Optimization**
  - [ ] Refactor jQuery code to use modern patterns
  - [ ] Optimize DOM manipulation operations
  - [ ] Implement event delegation where appropriate
  - [ ] Add debouncing for frequently triggered events

- [ ] **General Performance**
  - [ ] Implement scaffolding and formatting that support lazy loading for caching plugins
  - [ ] Optimize AJAX response size by returning only necessary data

## 5. User Experience Improvements

- [ ] **Error Handling**
  - [ ] Add user-friendly error messages
  - [ ] Create recovery paths from common error states
  - [ ] Add logging to WordPress error log for debugging

- [ ] **User Interface**
  - [ ] Add loading indicators for all AJAX operations
  - [ ] Implement success/error notifications for user actions
  - [ ] Ensure accessibility compliance (WCAG 2.1 AA)
  - [ ] Add keyboard navigation support

- [ ] **User Feedback**
  - [ ] Implement progress indicators for long-running operations
  - [ ] Add confirmation dialogs for destructive actions
  - [ ] Provide helpful tooltips for UI elements
  - [ ] Create inline help documentation

## 6. Maintainability Enhancements

- [ ] **Version Control**
  - [ ] Add inline documentation for future maintainers
  - [ ] Create a changelog with semantic versioning

- [ ] **Configuration**
  - [ ] Create a settings framework for plugin options
  - [ ] Add sensible defaults for all settings
  - [ ] Implement validation for settings values

## 7. Feature Enhancements (To Consider After Core Improvements)

- [ ] **Media Management**
  - [ ] Add bulk operations for media files
  - [ ] Implement drag-select for multiple files
  - [ ] Add advanced filtering options

- [ ] **Folder System**
  - [ ] Implement folder color coding
  - [ ] Add folder sharing/permissions
  - [ ] Create folder templates

- [ ] **Integration**
  - [ ] Add integration with popular page builders
  - [ ] Implement block editor block
  - [ ] Create REST API endpoints for programmatic access

- [ ] **Future Considerations**
  - [ ] Add custom hook system for extensibility
  - [ ] Implement pagination for folder contents
  - [ ] Create comprehensive documentation

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