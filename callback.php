<?php

session_start();
require_once "services/ui.php";
require_once "services/quiz.php";

file_put_contents('last.txt', file_get_contents('php://input'));