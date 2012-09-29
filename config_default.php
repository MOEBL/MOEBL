<?php
/*
 * MOEBL (My Online eBook Library)
 * -------------------------------
 * Copyright (c) My Online eBook Library
 *
 * DESCRIPTION:
 * Configuration class file.
 */

    if (!isset($config))
        $config = array();
    
    /**************************/
    /*** DIRECTORY SETTINGS ***/
    /**************************/
    /*
     * The directory containing calibre's metadata.db file, with sub-directories
     * containing all the book formats.
     * NOTE : It must end with a trailing  /
     * If this directory starts with a / then please make sure that the
     * calibre_internal_directory setting is set correctly. If not then EPUB
     * downloads may not work correctly.
     */
    $config['calibre_directory'] = './';
    
    /*
     * The internal directory to the location that calibre_directory points to.
     */
    $config['calibre_internal_directory'] = '/www/library/data/'; 

    /*
     * Full URL to the MOEBL directory (with trailing /)
     * NOTE : Required for OpenSearch and Mantano.
     */
    $config['moebl_full_url'] = 'www.myonlinebooksurl.com/library/'; 
    
    /*
     * Wich header to use when downloading books outside the web directory
     * Possible values are :
     *   X-Sendfile : For Lightttpd or Apache (with mod_xsendfile)
     *   X-Accel-Redirect : For Nginx
     */
    $config['moebl_x_accel_redirect'] = "X-Sendfile";
    
    
    /************************/
    /*** GENERAL SETTINGS ***/
    /************************/
    
    /* The title of your online library/catalog. */
    $config['moebl_library_title'] = "My Online eBook Library"; 

    /*
     * Default timezone.
     * Check the following link for other timezones :
     * http://www.php.net/manual/en/timezones.php
     */
    $config['moebl_default_timezone'] = "Europe/London";
    
    /* Number of recent books to show */
    $config['moebl_recentbooks_limit'] = '50'; 
    
    /*
     * Max number of items per page
     */
    $config['moebl_max_item_per_page'] = "-1";
    
    
    /******************************/
    /*** OPDS SPECIFIC SETTINGS ***/
    /******************************/
    
    /* Height of thumbnail images for OPDS. */
    $config['moebl_opds_thumbnail_height'] = "40";
    
    /*
     * Generate a invalid OPDS stream to allow the use of OpenSearch in bad OPDS clients.
     * Example of non compliant OPDS clients : FBReader (was working in May 2012), Moon+ Reader
     * Example of good OPDS clients : Mantano
     *  1 : enable support for non compliant OPDS clients
     *  0 : always generate valid OPDS code
     */
    $config['moebl_opds_generate_invalid_stream'] = "0"; 
    
    /*
     * Show icons for authors, series, tags and books on OPDS feed.
     *  1 : enable
     *  0 : disable
     */
    $config['moebl_opds_show_icons'] = "1";
    
    
    /******************************/
    /*** HTML SPECIFIC SETTINGS ***/
    /******************************/
    
    /* Height of thumbnail images for HTML. */
    $config['moebl_html_thumbnail_height'] = "70";
    
    /*
     * Prefered format(s) for the HTML library.
     * Only the first two will be displayed in the list of books while the
     * others only appear in the books details (when a book is selected).
     */
    $config['moebl_html_prefered_format'] = array ("EPUB", "PDF", "MOBI", "CBR", "CBZ");
    
    /*
     * Use URL rewriting for downloading eBooks from the HTML catalog.
     *  1 : enable
     *  0 : disable
     */
    $config['moebl_html_use_url_rewriting'] = "0";
    
  
?>