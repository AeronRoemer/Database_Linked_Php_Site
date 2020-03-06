<?php
//establish a connection
try { 
    $db = new PDO("sqlite:".__DIR__."/database.db");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e){
    echo "Error, cannot connetct to database: ";
    echo $e->getMessage();
    exit;
}

