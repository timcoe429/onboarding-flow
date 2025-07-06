# Elementor Site Cloner

A specialized WordPress multisite plugin for cloning Elementor-based template sites quickly and reliably.

## Features

- **Fast Database Cloning**: Direct database operations for speed
- **Smart URL Replacement**: Handles serialized data and JSON properly
- **File System Cloning**: Copies all uploads and Elementor assets
- **Elementor-Specific Processing**: Regenerates CSS and handles kit settings
- **Clean Admin Interface**: Simple UI for cloning sites
- **Progress Tracking**: Visual feedback during cloning
- **Error Handling**: Rollback on failure

## Requirements

- WordPress Multisite installation
- Elementor plugin (network activated or per-site)
- PHP 7.0+
- MySQL 5.6+

## Installation

1. Upload the `elementor-site-cloner` folder to `/wp-content/plugins/`
2. Network activate the plugin through the 'Network Admin > Plugins' menu
3. Navigate to 'Network Admin > Site Cloner' to start using

## Usage

1. Go to **Network Admin > Site Cloner**
2. Select a source site to clone
3. Enter a name for the new site
4. Enter the URL path or subdomain
5. Click "Start Cloning"
6. Wait for the process to complete
7. Visit the new site admin

## Architecture

The plugin consists of several specialized classes:

- **Clone Manager**: Orchestrates the entire cloning process
- **Database Cloner**: Handles database table cloning
- **URL Replacer**: Smart URL replacement respecting data formats
- **File Cloner**: Copies uploads and assets
- **Elementor Handler**: Elementor-specific post-processing
- **Admin Interface**: User interface for cloning

## How It Works

1. **Creates New Site**: Uses WordPress multisite APIs
2. **Clones Database**: Direct table copying with proper prefixes
3. **Updates URLs**: Intelligent replacement in serialized/JSON data
4. **Copies Files**: All uploads and Elementor-specific directories
5. **Processes Elementor**: Regenerates CSS, fixes kit settings
6. **Finalizes**: Clears caches, updates permalinks

## Advantages Over Generic Cloners

- **Elementor Optimized**: Handles Elementor's JSON data properly
- **Fast**: Direct database operations instead of export/import
- **Reliable**: Transaction support with rollback
- **Clean**: No unnecessary features or overhead

## Troubleshooting

### Clone Failed
- Check PHP error logs
- Ensure sufficient disk space
- Verify database permissions

### Elementor Designs Not Showing
- Visit Elementor > Tools > Regenerate CSS
- Clear browser cache
- Check if Elementor is active on new site

### URLs Not Updated
- Check if source URLs exist in database
- Verify new site URL is correct
- Manual search/replace may be needed for edge cases

## Support

For issues or feature requests, please check the error logs and ensure all requirements are met. 