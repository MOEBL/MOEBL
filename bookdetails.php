<?php
/*
 * MOEBL (My Online eBook Library)
 * -------------------------------
 * Copyright (c) My Online eBook Library
 *
 * DESCRIPTION:
 * Show book details.
 */

require_once ("config.php");
require_once ("books.php");

$book = Book::getBookById($_GET["id"]);
$authors = $book->getAuthors ();
$tags = $book->getTags ();
$serie = $book->getSerie ();
$book->getLinkArray ();
 
?>
<div class="bookpopup">
    <div class="booke">
        <div class="cover">
            <img src="fetch.php?id=<?php echo $book->id ?>&amp;height=150" alt="cover" />
        </div>
        <div class="download">
<?php
            foreach ($book->getDatas() as $data)
            {
?>    
                <div class="button buttonEffect"><a href="<?php echo $data->getHtmlLink () ?>"><?php echo $data->format ?></a></div>
<?php
            }
?>
        </div>
        <div class="entryTitle"><?php echo htmlspecialchars ($book->title) ?></div>
        <div class="entrySection">
            <span><?php echo localize("authors.title") ?></span>
            <div class="buttonEffect pad6">
<?php
            $i = 0;
            foreach ($authors as $author) {
                if ($i > 0) echo ", ";
?>
                <a href="index.php<?php echo str_replace ("&", "&amp;", $author->getUri ()) ?>"><?php echo htmlspecialchars ($author->name) ?></a>
<?php
            }
?>
            </div>
        </div>
        <div class="entrySection">
            <span><?php echo localize("tags.title") ?></span>
            <div class="buttonEffect pad6">
<?php
            $i = 0;
            foreach ($tags as $tag) {
                if ($i > 0) echo ", ";
?>
                <a href="index.php<?php echo str_replace ("&", "&amp;", $tag->getUri ()) ?>"><?php echo htmlspecialchars ($tag->name) ?></a>
<?php
            }
?>
            </div>
        </div>
<?php
        if (!is_null ($serie))
        {
?>
        <div class="entrySection">
            <div class="buttonEffect pad6">
                <a href="index.php<?php echo str_replace ("&", "&amp;", $serie->getUri ()) ?>"><?php echo localize("series.title") ?></a>
            </div>
            <?php echo str_format (localize ("content.series.data"), $book->seriesIndex, htmlspecialchars ($serie->name)) ?>
        </div>
<?php
        }
?>
    </div>
    <div class="clearer" />
    <hr />
    <div><?php echo localize("content.summary") ?></div>
    <div class="content"><?php echo $book->getComment (false) ?></div>
    <hr />
</div>