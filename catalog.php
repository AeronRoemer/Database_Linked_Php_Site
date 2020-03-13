<?php 
include("inc/functions.php");
$pageTitle = "Full Catalog";
$section = null;
$items_per_page = 8;

if (isset($_GET["cat"])) {
    if ($_GET["cat"] == "books") {
        $pageTitle = "Books";
        $section = "books";
    } else if ($_GET["cat"] == "movies") {
        $pageTitle = "Movies";
        $section = "movies";
    } else if ($_GET["cat"] == "music") {
        $pageTitle = "Music";
        $section = "music";
    }
}
if (isset($_GET["pg"])){
    $current_page = filter_input(INPUT_GET, "pg", FILTER_SANITIZE_NUMBER_INT);
} if (empty($current_page)) {
    $current_page =1;
}

$total_items = get_catalog_count($section);
$total_page = ceil($total_items/$items_per_page);
$offset = ($current_page - 1) * $items_per_page;
//limit results when on category
$limit_results = "";
if (!empty($section)){
    $limit_results = "cat=" . $section. "&";
}

//redirects for too-large and too-small
if ($current_page > $total_page){
    header("location:catalog.php".$limit_results."?pg=".$total_page);
}
if ($current_page < 1){
    header("location:catalog.php?".$limit_results."?pg=1");
}

if(empty($section)){
    $category = full_catalog_array($items_per_page, $offset);
} else {
    $category = one_category_array($section, $items_per_page, $offset);
}

$pagination = "<div class=\"pagination\">";
$pagination .= "Pages:";
for ($i = 1; $i <= $total_page; $i++){
if ($i == $current_page){
    $pagination .= "<span> $i </span>";
} else {
    $pagination .= "<a href='catalog.php?";
    if (!empty($section)){
        $pagination .= "cat=".$section."&";
                }
                $pagination .= "pg=$i'>$i</a> ";

}}
$pagination .= "</div>";

include("inc/header.php"); ?>

<div class="section catalog page">
    
    <div class="wrapper">
        
        <h1><?php 
        if ($section != null) {
            echo "<a href='catalog.php'>Full Catalog</a> &gt; ";
        }
        echo $pageTitle; ?></h1>
        <?php echo $pagination; ?>
        <ul class="items">
            <?php
            foreach ($category as $item) {
                echo get_item_html($item);
            }
            ?>
        </ul>
     <?php echo $pagination; ?>
    </div>
</div>

<?php include("inc/footer.php"); ?>