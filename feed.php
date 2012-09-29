<?php
/*
 * MOEBL (My Online eBook Library)
 * -------------------------------
 * Copyright (c) My Online eBook Library
 *
 * DESCRIPTION:
 * Main OPDS file.
 */

    require_once ("config.php");
    require_once ("base.php");
    require_once ("authors.php");
    require_once ("series.php");
    require_once ("tags.php");
    require_once ("books.php");
    require_once ("OPDS_renderer.php");
    
    header ("Content-Type:application/xml");
    $page = getURLParam ("page", Base::PAGE_INDEX);
    $query = getURLParam ("query");
    $n = getURLParam ("n", "1");
    if ($query)
        $page = Base::PAGE_OPENSEARCH_QUERY;
    $qid = getURLParam ("id");
    
    $OPDSRender = new OPDSRenderer ();
    
    switch ($page) {
        case Base::PAGE_OPENSEARCH :
            echo $OPDSRender->getOpenSearch ();
            return;
        default:
            $currentPage = Page::getPage ($page, $qid, $query, $n);
            $currentPage->InitializeContent ();
            echo $OPDSRender->render ($currentPage);
            return;
            break;
    }
?>
