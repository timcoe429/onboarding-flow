<?php
/**
 * File Cloner Class
 * Handles copying all necessary files between sites
 */
class ESC_File_Cloner {
    
    private $source_site_id;
    private $destination_site_id;
    private $source_upload_dir;
    private $destination_upload_dir;
    private $batch_size = 100;
    
    public function __construct($source_site_id, $destination_site_id) {
        $this->source_site_id = $source_site_id;
        $this->destination_site_id = $destination_site_id;
        
        // Get upload directories
        switch_to_blog($source_site_id);
        $upload_dir = wp_upload_dir();
        $this->source_upload_dir = $upload_dir['basedir'];
        restore_current_blog();
        
        switch_to_blog($destination_site_id);
        $upload_dir = wp_upload_dir();
        $this->destination_upload_dir = $upload_dir['basedir'];
        restore_current_blog();
    }
    
    /**
     * Clone all files from source to destination
     */
    public function clone_files() {
        try {
            // Create destination upload directory if it doesn't exist
            if (!file_exists($this->destination_upload_dir)) {
                wp_mkdir_p($this->destination_upload_dir);
            }
            
            // Clone main uploads directory
            $this->clone_directory($this->source_upload_dir, $this->destination_upload_dir);
            
            // Clone Elementor-specific directories
            $this->clone_elementor_files();
            
            // Clone theme customizations if they exist
            $this->clone_theme_customizations();
            
            return true;
            
        } catch (Exception $e) {
            return new WP_Error('file_clone_failed', $e->getMessage());
        }
    }
    
    /**
     * Clone a directory recursively
     */
    private function clone_directory($source, $destination) {
        // Skip if source doesn't exist
        if (!is_dir($source)) {
            return;
        }
        
        // Create destination directory
        if (!file_exists($destination)) {
            mkdir($destination, 0755, true);
        }
        
        // Use iterator for better memory management with large directories
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        $file_count = 0;
        
        foreach ($iterator as $item) {
            $target = $destination . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
            
            if ($item->isDir()) {
                // Create directory
                if (!file_exists($target)) {
                    mkdir($target, 0755, true);
                }
            } else {
                // Copy file
                $this->copy_file($item->getPathname(), $target);
                
                // Process in batches to avoid memory issues
                $file_count++;
                if ($file_count % $this->batch_size === 0) {
                    // Give the system a breather
                    if (function_exists('wp_cache_flush')) {
                        wp_cache_flush();
                    }
                }
            }
        }
    }
    
    /**
     * Copy a single file with error handling
     */
    private function copy_file($source, $destination) {
        // Create destination directory if needed
        $dest_dir = dirname($destination);
        if (!file_exists($dest_dir)) {
            mkdir($dest_dir, 0755, true);
        }
        
        // Skip if destination already exists and is newer
        if (file_exists($destination) && filemtime($destination) >= filemtime($source)) {
            return;
        }
        
        // Copy the file
        if (!@copy($source, $destination)) {
            // Try alternative method for large files
            $this->copy_large_file($source, $destination);
        }
        
        // Preserve file permissions
        @chmod($destination, fileperms($source));
    }
    
    /**
     * Copy large files in chunks
     */
    private function copy_large_file($source, $destination) {
        $chunk_size = 1048576; // 1MB chunks
        
        $source_handle = @fopen($source, 'rb');
        $dest_handle = @fopen($destination, 'wb');
        
        if (!$source_handle || !$dest_handle) {
            throw new Exception("Failed to open file for copying: $source");
        }
        
        while (!feof($source_handle)) {
            $chunk = fread($source_handle, $chunk_size);
            fwrite($dest_handle, $chunk);
        }
        
        fclose($source_handle);
        fclose($dest_handle);
    }
    
    /**
     * Clone Elementor-specific files
     */
    private function clone_elementor_files() {
        // Elementor stores CSS in uploads/elementor/css/
        $elementor_dirs = array(
            'elementor/css',
            'elementor/fonts',
            'elementor/custom-icons',
            'elementor-custom-fonts'
        );
        
        foreach ($elementor_dirs as $dir) {
            $source_dir = $this->source_upload_dir . '/' . $dir;
            $dest_dir = $this->destination_upload_dir . '/' . $dir;
            
            if (is_dir($source_dir)) {
                $this->clone_directory($source_dir, $dest_dir);
            }
        }
    }
    
    /**
     * Clone theme customizations
     */
    private function clone_theme_customizations() {
        // Get active theme from source site
        switch_to_blog($this->source_site_id);
        $theme = get_option('stylesheet');
        $theme_mods = get_option("theme_mods_{$theme}");
        restore_current_blog();
        
        // Set theme mods on destination site
        if ($theme_mods) {
            switch_to_blog($this->destination_site_id);
            update_option("theme_mods_{$theme}", $theme_mods);
            restore_current_blog();
        }
        
        // Clone custom CSS if it exists
        $this->clone_custom_css();
    }
    
    /**
     * Clone custom CSS
     */
    private function clone_custom_css() {
        // Get custom CSS from source
        switch_to_blog($this->source_site_id);
        $custom_css = wp_get_custom_css();
        restore_current_blog();
        
        if (!empty($custom_css)) {
            switch_to_blog($this->destination_site_id);
            wp_update_custom_css_post($custom_css);
            restore_current_blog();
        }
    }
    
    /**
     * Get total size of files to be cloned
     */
    public static function get_clone_size($source_site_id) {
        switch_to_blog($source_site_id);
        $upload_dir = wp_upload_dir();
        $source_dir = $upload_dir['basedir'];
        restore_current_blog();
        
        return self::get_directory_size($source_dir);
    }
    
    /**
     * Get directory size recursively
     */
    private static function get_directory_size($directory) {
        $size = 0;
        
        if (!is_dir($directory)) {
            return $size;
        }
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }
        
        return $size;
    }
    
    /**
     * Clean up orphaned files in destination
     */
    public function cleanup_orphaned_files() {
        // This would remove files that exist in destination but not in source
        // Useful for keeping clones clean, but optional
        // Implementation depends on specific needs
    }
}
