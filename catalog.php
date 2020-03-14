<?php 
include("inc/functions.php");
$pageTitle = "Full Catalog";
$section = null;
$search = null;
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
//sets curent page variable etc
if (isset($_GET["s"])){
    $search = filter_input(INPUT_GET, "s", FILTER_SANITIZE_STRING);
} 
if (isset($_GET["pg"])){
    $current_page = filter_input(INPUT_GET, "pg", FILTER_SANITIZE_NUMBER_INT);
} 
if (empty($current_page)) {
    $current_page =1;
}


//if total items are 0, don't do the following
$total_items = get_catalog_count($section, $search);
$total_pages = 1;
$offset = 0;
if ($total_items > 0){

    $total_pages = ceil($total_items/$items_per_page);
    
    //limit results when on category
    $limit_results = "";
    if(!empty($search)){
        $limit_results = "s=".urlencode(htmlspecialchars($search))."&";
    } else if (!empty($section)){
        $limit_results = "cat=" . $section. "&";
    }

    if ($current_page > $total_pages){
        header("location:catalog.php"
        .$limit_results."?pg=".$total_page);
    }

    if ($current_page < 1){
        header("location:catalog.php?".$limit_results."?pg=1");
    }
    $offset = ($current_page - 1) * $items_per_page;
    $pagination = "<div class=\"pagination\">";
    $pagination .= "Pages:";
    for ($i = 1; $i <= $total_pages; $i++){
    if ($i == $current_page){
        $pagination .= "<span> $i </span>";
    } else {
        $pagination .= "<a href='catalog.php?";
        if(!empty($search)){
            $pagination .= "s=".urlencode(htmlspecialchars($search))."&";
        } else if (!empty($section)){
            $pagination .= "cat=".$section."&";
                    }
                    $pagination .= "pg=$i'>$i</a> ";
    
    }}
    $pagination .= "</div>";
}
//redirects for too-large


if (!empty($search)) {
    $category = search_catalog_array($search, $items_per_page, $offset);
}else if(empty($section)){
    $category = full_catalog_array($items_per_page, $offset);
} else {
    $category = one_category_array($section, $items_per_page, $offset);
}

include("inc/header.php"); ?>

<div class="section catalog page">
    
    <div class="wrapper">
        
        <h1><?php 
        if ($search != null){
            echo "Search results for: '" . htmlspecialchars($search)."'";
        } else {
            if ($section != null) {
                echo "<a href='catalog.php'>Full Catalog</a> &gt; ";
            }
        }
        ?></h1>
        <?php 
            if ($total_items < 1){
                echo "<p>No items were found.</p>";
                echo "<p>Search again or browse full catalog.</p>";
                echo "<a href=\'catalog.php\'> Full Catalog </a>";
            } else {
                echo $pagination;
            
             ?>
        <ul class="items">
            <?php

            foreach ($category as $item) {
                echo get_item_html($item);
            }
            ?>
        </ul>
     <?php echo $pagination; }?>
    </div>
</div>

<?php include("inc/footer.php"); ?>