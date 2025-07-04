<?php
/**
 * Simple debug script to identify why topic data is not loading
 * Access via: yourdomain.com/wp-content/plugins/media-kit-content-generator/debug-topic-loading.php?entry=32372
 */

// Find WordPress
$wp_path = '../../../wp-load.php';
if (!file_exists($wp_path)) {
    $wp_path = '../../../../wp-load.php';
}
if (!file_exists($wp_path)) {
    die