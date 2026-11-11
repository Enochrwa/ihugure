<?php

require_once 'services/db.php';
require_once 'services/momo.php';

$rows = query_rows('SELECT id FROM game WHERE payment_status IS NULL OR payment_status = "PENDING" ORDER BY id DESC');

foreach($rows as $row) {
    try{
    query_update('game', ['payment_status'=>momo_check_payment($row['id'])], ['id'=>$row['id']]);
    }catch(Exception $e) {
        
    }
    //echo "{$row['id']}<br/>";
}