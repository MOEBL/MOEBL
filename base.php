<?php
/*
 * MOEBL (My Online eBook Library)
 * -------------------------------
 * Copyright (c) My Online eBook Library
 *
 * DESCRIPTION:
 * Base class file.
 */

define ("VERSION", "0.0.1");
date_default_timezone_set($config['moebl_default_timezone']);
 
function getURLParam ($name, $default = NULL) {
    if (!empty ($_GET) && isset($_GET[$name])) {
        return $_GET[$name];
    }
    return $default;
}

function getUrlWithVersion ($url) {
    return $url . "?v=" . VERSION;
}

/**
 * This method is a direct copy-paste from
 * http://tmont.com/blargh/2010/1/string-format-in-php
 */
function str_format($format) {
    $args = func_get_args();
    $format = array_shift($args);
    
    preg_match_all('/(?=\{)\{(\d+)\}(?!\})/', $format, $matches, PREG_OFFSET_CAPTURE);
    $offset = 0;
    foreach ($matches[1] as $data) {
        $i = $data[0];
        $format = substr_replace($format, @$args[$i], $offset + $data[1] - 1, 2 + strlen($i));
        $offset += strlen(@$args[$i]) - 2 - strlen($i);
    }
    
    return $format;
}

/**
 * This method is based on this page
 * http://www.mind-it.info/2010/02/22/a-simple-approach-to-localization-in-php/
 */
function localize($phrase) {
    /* Static keyword is used to ensure the file is loaded only once */
    static $translations = NULL;
    /* If no instance of $translations has occured load the language file */
    if (is_null($translations)) {
        $lang = "en";
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
        {
            $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
        }
        $lang_file_en = NULL;
        $lang_file = 'lang/lang_' . $lang . '.loc';
        if (!file_exists($lang_file)) {
            $lang_file = 'lang/' . 'lang_en.loc';
        }
        elseif ($lang != "en") {
            $lang_file_en = 'lang/' . 'lang_en.loc';
        }
        $lang_file_content = file_get_contents($lang_file);
        /* Load the language file as a JSON object and transform it into an associative array */
        $translations = json_decode($lang_file_content, true);
        if ($lang_file_en)
        {
            $lang_file_content = file_get_contents($lang_file_en);
            $translations_en = json_decode($lang_file_content, true);
            $translations = array_merge ($translations_en, $translations);
        }
    }
    return $translations[$phrase];
}

class Link
{
    const OPDS_THUMBNAIL_TYPE = "http://opds-spec.org/image/thumbnail";
    const OPDS_IMAGE_TYPE = "http://opds-spec.org/image";
    const OPDS_ACQUISITION_TYPE = "http://opds-spec.org/acquisition";
    const OPDS_NAVIGATION_TYPE = "application/atom+xml;profile=opds-catalog;kind=navigation";
    const OPDS_PAGING_TYPE = "application/atom+xml;profile=opds-catalog;kind=acquisition";
    
    public $href;
    public $type;
    public $rel;
    public $title;
    
    public function __construct($phref, $ptype, $prel = NULL, $ptitle = NULL) {
        $this->href = $phref;
        $this->type = $ptype;
        $this->rel = $prel;
        $this->title = $ptitle;
    }
    
    public function hrefXhtml () {
        return str_replace ("&", "&amp;", $this->href);
    }
}

class LinkNavigation extends Link
{
    public function __construct($phref, $prel = NULL, $ptitle = NULL) {
        parent::__construct ($phref, Link::OPDS_NAVIGATION_TYPE, $prel, $ptitle);
        $this->href = $_SERVER["SCRIPT_NAME"] . $this->href;
    }
}


class Entry
{
    public $title;
    public $id;
    public $content;
    public $contentType;
    public $linkArray;
    public $localUpdated;
    private static $updated = NULL;
    
    public static $icons = array(
        Author::ALL_AUTHORS_ID    => 'images/author16.png',
        Serie::ALL_SERIES_ID      => 'images/series16.png',
        Book::ALL_RECENT_BOOKS_ID => 'images/recent16.png',
        Tag::ALL_TAGS_ID          => 'images/tags16.png',
        "calibre:books$"          => 'images/allbooks16.png',
        "calibre:books:letter"    => 'images/allbooks16.png'
    );
    
    public function getUpdatedTime () {
        if (!is_null ($this->localUpdated)) {
            return date (DATE_ATOM, $this->localUpdated);
        }
        if (is_null (self::$updated)) {
            self::$updated = time();
        }
        return date (DATE_ATOM, self::$updated);
    }
 
    public function __construct($ptitle, $pid, $pcontent, $pcontentType, $plinkArray) {
        global $config;
        $this->title = $ptitle;
        $this->id = $pid;
        $this->content = $pcontent;
        $this->contentType = $pcontentType;
        $this->linkArray = $plinkArray;
        
        if ($config['moebl_opds_show_icons'] == 1)
        {
            foreach (self::$icons as $reg => $image)
            {
                if (preg_match ("/" . $reg . "/", $pid)) {
                    array_push ($this->linkArray, new Link (getUrlWithVersion ($image), "image/png", Link::OPDS_THUMBNAIL_TYPE));
                    break;
                }
            }
        }
    }
}

class EntryBook extends Entry
{
    public $book;
    
    public function __construct($ptitle, $pid, $pcontent, $pcontentType, $plinkArray, $pbook) {
        parent::__construct ($ptitle, $pid, $pcontent, $pcontentType, $plinkArray);
        $this->book = $pbook;
        $this->localUpdated = $pbook->timestamp;
    }
    
    public function getCoverThumbnail () {
        foreach ($this->linkArray as $link) {
            if ($link->rel == Link::OPDS_THUMBNAIL_TYPE)
                return $link->hrefXhtml ();
        }
        return null;
    }
    
    public function getCover () {
        foreach ($this->linkArray as $link) {
            if ($link->rel == Link::OPDS_IMAGE_TYPE)
                return $link->hrefXhtml ();
        }
        return null;
    }
}

class Page
{
    public $title;
    public $idPage;
    public $idGet;
    public $query;
    public $n;
    public $totalNumber = -1;
    public $entryArray = array();
    
    public static function getPage ($pageId, $id, $query, $n)
    {
        switch ($pageId) {
            case Base::PAGE_ALL_AUTHORS :
                return new PageAllAuthors ($id, $query, $n);
            case Base::PAGE_AUTHOR_DETAIL :
                return new PageAuthorDetail ($id, $query, $n);
            case Base::PAGE_ALL_TAGS :
                return new PageAllTags ($id, $query, $n);
            case Base::PAGE_TAG_DETAIL :
                return new PageTagDetail ($id, $query, $n);
            case Base::PAGE_ALL_SERIES :
                return new PageAllSeries ($id, $query, $n);
            case Base::PAGE_ALL_BOOKS :
                return new PageAllBooks ($id, $query, $n);
            case Base::PAGE_ALL_BOOKS_LETTER:
                return new PageAllBooksLetter ($id, $query, $n);
            case Base::PAGE_ALL_RECENT_BOOKS :
                return new PageRecentBooks ($id, $query, $n);
            case Base::PAGE_SERIE_DETAIL : 
                return new PageSerieDetail ($id, $query, $n);
            case Base::PAGE_OPENSEARCH_QUERY :
                return new PageQueryResult ($id, $query, $n);
                break;
            default:
                $page = new Page ($id, $query, $n);
                $page->idPage = "moebl:catalog";
                return $page;
        }
    }
    
    public function __construct($pid, $pquery, $pn) {
        $this->idGet = $pid;
        $this->query = $pquery;
        $this->n = $pn;
    }
    
    public function InitializeContent () 
    {
        global $config;
        $this->title = $config['moebl_library_title'];
        array_push ($this->entryArray, Author::getCount());
        array_push ($this->entryArray, Serie::getCount());
        array_push ($this->entryArray, Tag::getCount());
        $this->entryArray = array_merge ($this->entryArray, Book::getCount());
    }
    
    public function isPaginated ()
    {
        global $config;
        return ($config['moebl_max_item_per_page'] != -1 && $this->totalNumber != -1);
    }
    
    public function getNextLink ()
    {
        global $config;
        $currentUrl = $_SERVER['QUERY_STRING'];
        $currentUrl = preg_replace ("/\&n=.*?$/", "", "?" . $_SERVER['QUERY_STRING']);
        if (($this->n) * $config['moebl_max_item_per_page'] < $this->totalNumber) {
            return new LinkNavigation ($currentUrl . "&n=" . ($this->n + 1), "next", "Next Page");
        }
        return NULL;
    }
    
    public function getPrevLink ()
    {
        global $config;
        $currentUrl = $_SERVER['QUERY_STRING'];
        $currentUrl = preg_replace ("/\&n=.*?$/", "", "?" . $_SERVER['QUERY_STRING']);
        if ($this->n > 1) {
            return new LinkNavigation ($currentUrl . "&n=" . ($this->n - 1), "previous", "Previous Page");
        }
        return NULL;
    }

}

class PageAllAuthors extends Page
{
    public function InitializeContent () 
    {
        $this->title = localize("authors.title");
        $this->entryArray = Author::getAllAuthors();
        $this->idPage = Author::ALL_AUTHORS_ID;
    }
}

class PageAuthorDetail extends Page
{
    public function InitializeContent () 
    {
        $author = Author::getAuthorById ($this->idGet);
        $this->idPage = $author->getEntryId ();
        $this->title = $author->name;
        list ($this->entryArray, $this->totalNumber) = Book::getBooksByAuthor ($this->idGet, $this->n);
    }
}

class PageAllTags extends Page
{
    public function InitializeContent () 
    {
        $this->title = localize("tags.title");
        $this->entryArray = Tag::getAllTags();
        $this->idPage = Tag::ALL_TAGS_ID;
    }
}

class PageTagDetail extends Page
{
    public function InitializeContent () 
    {
        $tag = Tag::getTagById ($this->idGet);
        $this->idPage = $tag->getEntryId ();
        $this->title = $tag->name;
        list ($this->entryArray, $this->totalNumber) = Book::getBooksByTag ($this->idGet, $this->n);
    }
}

class PageAllSeries extends Page
{
    public function InitializeContent () 
    {
        $this->title = localize("series.title");
        $this->entryArray = Serie::getAllSeries();
        $this->idPage = Serie::ALL_SERIES_ID;
    }
}

class PageSerieDetail extends Page
{
    public function InitializeContent () 
    {
        $serie = Serie::getSerieById ($this->idGet);
        $this->title = $serie->name;
        list ($this->entryArray, $this->totalNumber) = Book::getBooksBySeries ($this->idGet, $this->n);
        $this->idPage = $serie->getEntryId ();
    }
}

class PageAllBooks extends Page
{
    public function InitializeContent () 
    {
        $this->title = localize ("allbooks.title");
        $this->entryArray = Book::getAllBooks ();
        $this->idPage = Book::ALL_BOOKS_ID;
    }
}

class PageAllBooksLetter extends Page
{
    public function InitializeContent () 
    {
        $this->title = str_format (localize ("splitByLetter.letter"), localize ("bookword.title"), $this->idGet);
        list ($this->entryArray, $this->totalNumber) = Book::getBooksByStartingLetter ($this->idGet, $this->n);
        $this->idPage = Book::getEntryIdByLetter ($this->idGet);
    }
}

class PageRecentBooks extends Page
{
    public function InitializeContent () 
    {
        $this->title = localize ("recent.title");
        $this->entryArray = Book::getAllRecentBooks ();
        $this->idPage = Book::ALL_RECENT_BOOKS_ID;
    }
}

class PageQueryResult extends Page
{
    public function InitializeContent () 
    {
        $this->title = "Search result for query *" . $this->query . "*"; // TODO I18N
        list ($this->entryArray, $this->totalNumber) = Book::getBooksByQuery ($this->query, $this->n);
    }
}

abstract class Base
{
    const PAGE_INDEX = "index";
    const PAGE_ALL_AUTHORS = "1";
    const PAGE_AUTHORS_FIRST_LETTER = "2";
    const PAGE_AUTHOR_DETAIL = "3";
    const PAGE_ALL_BOOKS = "4";
    const PAGE_ALL_BOOKS_LETTER = "5";
    const PAGE_ALL_SERIES = "6";
    const PAGE_SERIE_DETAIL = "7";
    const PAGE_OPENSEARCH = "8";
    const PAGE_OPENSEARCH_QUERY = "9";
    const PAGE_ALL_RECENT_BOOKS = "10";
    const PAGE_ALL_TAGS = "11";
    const PAGE_TAG_DETAIL = "12";

    const COMPATIBILITY_XML_ALDIKO = "aldiko";
    
    private static $db = NULL;
    
    public static function getDb () {
        global $config;
        if (is_null (self::$db)) {
            try {
                self::$db = new PDO('sqlite:'. $config['calibre_directory'] .'metadata.db');
            } catch (Exception $e) {
                echo $e;
                die($e);
            }
        }
        return self::$db;
    }
    
    public static function executeQuery($query, $columns, $params, $n) {
        global $config;
        $totalResult = -1;
        
        if ($config['moebl_max_item_per_page'] != -1)
        {
            // First check total number of results
            $result = self::getDb ()->prepare (str_format ($query, "count(*)"));
            $result->execute ($params);
            $totalResult = $result->fetchColumn ();
            
            // Next modify the query and params
            $query .= " limit ?, ?";
            array_push ($params, ($n - 1) * $config['moebl_max_item_per_page'], $config['moebl_max_item_per_page']);
        }
        
        $result = self::getDb ()->prepare(str_format ($query, $columns));
        $result->execute ($params);
        return array ($totalResult, $result);
    }

}
?>