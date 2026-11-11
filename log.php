<?php
session_start();
require_once 'services/db.php';
require_once 'services/quiz.php';

$events = [
    '1'=>'DEBUG_OPENNED',
    '2'=>'OUT_OF_WINDOW',
    '3'=>'DOCUMENT_HIDDEN',
    '4'=>'BROWSER_TIMEOUT',
    ];
    
$event = $_GET['event'];

if(is_numeric($event) && isset($events[$event])) {
    game_log($events[$event]);
    if($event==4 && current_screen()==8) {
        query_update('game', ['state'=>'OUT_OF_SCREEN'], ['id'=>$_SESSION['gameid']]);
        $_SESSION = [];
    }
} else {
    game_log('HACKING_ATTEMPT', $event);
}

