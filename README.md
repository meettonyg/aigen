# Media Kit Content Generator - Simplified Architecture

## Overview

The Media Kit Content Generator is a WordPress plugin that helps users generate content for media kits, including topics and questions generators with formidable form integration.

## Project Structure

- `media-kit-content-generator/` - Main plugin directory
  - `assets/` - JavaScript, CSS, and other assets
  - `includes/` - PHP classes and core functionality
  - `templates/` - Template files for displaying content

- `ARCHIVE/` - Contains archived files, backups, test files, and documentation
  - `backups/` - Backup versions of files
  - `debug/` - Debug and diagnostic files
  - `docs/` - Documentation and implementation logs
  - `tests/` - Test files and validation scripts
  - `scripts/` - Shell scripts for automation
  - `implementation-reports/` - Implementation status reports
  - `legacy-code/` - Legacy code that has been replaced
  - See ARCHIVE/README.md for details on the archive structure

## Implementation Status

The codebase is currently undergoing a root-level simplification to address architectural debt and over-engineering issues. The simplification plan follows a three-phase approach:

1. **Phase 1: Core Architecture Simplification (PHP)**
   - Remove dual systems
   - Simplify error handling
   - Test phase 1 changes

2. **Phase 2: JavaScript Simplification**
   - Remove enhanced AJAX system
   - Simplify topics generator initialization
   - Add simple notification system
   - Test phase 2 changes

3. **Phase 3: Smart Simplification**
   - Replace MKCG_DataManager with simple event bus
   - Update cross-generator communication
   - Simplify enhanced UI feedback
   - Remove truly unused modules
   - Final testing and validation

## Documentation

All documentation has been moved to the `ARCHIVE/docs/` directory.

## Archived Code

All legacy code, backups, and test files have been moved to the `ARCHIVE/` directory to maintain a clean and focused main codebase.
