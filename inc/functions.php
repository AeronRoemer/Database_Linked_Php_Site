<?php

function array_category($catalog,$category) {
    $output = array();
    
    foreach ($catalog as $id => $item) {
        if ($category == null OR strtolower($category) == strtolower($item["category"])) {
            $sort = $item["title"];
            $sort = ltrim($sort,"The ");
            $sort = ltrim($sort,"A ");
            $sort = ltrim($sort,"An ");
            $output[$id] = $sort;            
        }
    }
    
    asort($output);
    return array_keys($output);
}  


//genre retrieve function
function genre_array($category = null){
    $category = strtolower($category);
    include('connection.php');
    try {
        $query = "SELECT genre, category
        FROM Genres
        JOIN Genre_Categories
        ON Genres.genre_id = Genre_Categories.genre_id
        ";
        if (!empty($category)){
            $results = $db->prepare($query . " WHERE LOWER(category) = ? ORDER BY genre;");
            $results->bindParam(1,$category,PDO::PARAM_STR);
        } else {
        $results = $db->prepare($query . " ORDER BY genre;");
    }
        $results->execute();
    }
    catch (Exception $e){
        echo "bad query";
        exit;
      }
   $genres = array();
   //while there is data in a row, fetch it
   while($row = $results->fetch(PDO::FETCH_ASSOC)){
    $genres[$row["category"]][] = $row["genre"];
  }  
  return $genres;
}

//optional pagination function with null as default
function get_catalog_count($category = null, $search = null){
    //connection to database in other file
    include('connection.php');
    $category = strtolower($category);
    try {
        $sql = "SELECT COUNT(media_id) FROM Media";
        if(!empty($search)){
       $result = $db->prepare($sql . " WHERE title LIKE ?");
        $result->bindValue(1, "%".$search."%", PDO::PARAM_STR);
        } else if (!empty($category)){
        $result = $db->prepare($sql . " WHERE LOWER(category) = ?");
        $result->bindParam(1, $category, PDO::PARAM_STR);
    } else {
        $result = $db->prepare($sql);
    }
    $result->execute();
} catch (Exception $e){
    echo "bad query";
    exit;
  }
  $count = $result->fetchColumn(0);
  return $count;
}

//fetch entire catalog from database results 
function full_catalog_array($limit = 0, $offset = 0){
    include('connection.php');
    try {
        $query = "SELECT media_id, title, category, img FROM Media
        ORDER BY REPLACE(
                REPLACE(
                    REPLACE(title, 'A ', '')
                    , 'An ', ''),
                 'The ', '')";
        if (is_integer($limit)){
            $results = $db->prepare($query . " LIMIT ? OFFSET ?");
            $results->bindParam(1, $limit, PDO::PARAM_INT);
            $results->bindParam(2, $offset, PDO::PARAM_INT);
        }     
        $results->execute();
      } catch (Exception $e){
        echo "No Results";
        exit;
      }
  $catalog = $results->fetchAll(PDO::FETCH_ASSOC);
  return $catalog;
}

//fetch random items from database results 
function rand_catalog_array(){
    include('connection.php');
    try {
        $results = $db->query(
            "SELECT media_id, title, category, img 
            FROM Media 
            ORDER BY RANDOM()
            LIMIT 4");
      } catch (Exception $e){
        echo "No Results";
        exit;
      }
  $catalog = $results->fetchAll(PDO::FETCH_ASSOC);
  return $catalog;
}

//fetch one category from database results 
function one_category_array($category, $limit = 0, $offset = 0){
    include('connection.php');
    $category = strtolower($category);
    try {
        $query = "SELECT media_id, title, category, img 
            FROM Media
            WHERE LOWER(category) = ?
            ORDER BY REPLACE(
                REPLACE(
                    REPLACE(title, 'A ', '')
                    , 'An ', ''),
                 'The ', '')";
            if (is_integer($limit)){
                $results = $db->prepare($query . " LIMIT ? OFFSET ?");
                $results->bindParam(1, $category, PDO::PARAM_STR);
                $results->bindParam(2, $limit, PDO::PARAM_INT);
                $results->bindParam(3, $offset, PDO::PARAM_INT);
            } else {
                $results = $db->prepare($query);
                $results->bindParam(1, $category, PDO::PARAM_STR);
            }
            $results->execute();
      } catch (Exception $e){
        echo "No Results";
        exit;
      }
  $catalog = $results->fetchAll(PDO::FETCH_ASSOC);
  return $catalog;
}

//fetch according to search terms
function search_catalog_array($search, $limit = null, $offset = 0){
    include('connection.php');
    try {
        $query = "SELECT media_id, title, category, img 
            FROM Media
            WHERE title LIKE ?
            ORDER BY REPLACE(
                REPLACE(
                    REPLACE(title, 'A ', '')
                    , 'An ', ''),
                 'The ', '')";
            if (is_integer($limit)){
                $results = $db->prepare($query . " LIMIT ? OFFSET ?");
                $results->bindValue(1, "%".$search."%", PDO::PARAM_STR);
                $results->bindParam(2, $limit, PDO::PARAM_INT);
                $results->bindParam(3, $offset, PDO::PARAM_INT);
            } else {
                $results = $db->prepare($query);
                $results->bindValue(1, "%".$search."%", PDO::PARAM_STR);
            }
            $results->execute();
      } catch (Exception $e){
        echo "No Results";
        exit;
      }
  $catalog = $results->fetchAll(PDO::FETCH_ASSOC);
  return $catalog;
}

//fetch single items from database results 
function one_item_array($id){
    include('connection.php');
    try {
        $results = $db->prepare(
            "SELECT Media.media_id, title, category, img, format, year, 
            publisher, isbn, genre 
            FROM Media
            JOIN Genres ON Media.genre_id=Genres.genre_id
            LEFT OUTER JOIN Books 
            ON Media.media_id = Books.media_id
            WHERE Media.media_id = ?"  
            );
            $results->bindParam(1, $id, PDO::PARAM_INT);
            $results->execute();
      } catch (Exception $e){
        echo "No Results";
        exit;
      }
  $item = $results->fetch();
  if (empty($item)) return $item;
    try {
        $results = $db->prepare(
            "SELECT fullname, role
            FROM Media_People
            JOIN People ON Media_People.people_id=People.people_id
            WHERE media_people.media_id = ?"  
            );
            $results->bindParam(1, $id, PDO::PARAM_INT);
            $results->execute();
      } catch (Exception $e){
        echo "No Results";
        exit;
      }
      while($row = $results->fetch(PDO::FETCH_ASSOC)){
        $item[$row["role"]][] = $row["fullname"];
      }  
      return $item;
}

function get_item_html($item) {
    $output = "<li><a href='details.php?id="
        . $item["media_id"] . "'><img src='" 
        . $item["img"] . "' alt='" 
        . $item["title"] . "' />" 
        . "<p>View Details</p>"
        . "</a></li>";
    return $output;
}