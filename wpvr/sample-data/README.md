# WPVR Sample Tour Data

This directory contains the sample tour data that gets imported when the WPVR plugin is activated for the first time.

## File Structure

- `wpvr_11852.json` - Main tour configuration file containing:
  - Tour title and settings
  - Scene definitions
  - Hotspot configurations
  - Tour metadata

- `S1.jpg`, `S2.jpg`, `S3.jpg`, `S4.jpg` - Scene images for the sample tour
  - S1: Living room scene (default scene)
  - S2: Stairs view scene
  - S3: Bedroom scene
  - S4: Kids room scene

## How It Works

When the WPVR plugin is activated:

1. The system checks if any existing WPVR tours exist
2. If no tours exist, it automatically imports the sample tour
3. Files are copied from this directory to the WordPress media library
4. A new post of type `wpvr_item` is created with the tour data
5. Scene images are processed and linked to the tour

## WordPress.org Compliance

This approach is fully compliant with WordPress.org plugin guidelines:
- No ZIP file extraction at runtime
- No prohibited file operations
- Files are included directly in the plugin structure
- Uses standard WordPress media handling functions

## Customization

To customize the sample tour:
1. Replace the image files with your own panoramic images
2. Update the `wpvr_11852.json` file with new tour configuration
3. Ensure scene IDs in the JSON match the image filenames

## File Requirements

- Scene images should be equirectangular panoramic images
- Recommended resolution: 2048x1024 or higher
- Supported formats: JPG, PNG
- JSON file must be valid JSON format
