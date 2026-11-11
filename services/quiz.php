<?php

	define('LEVEL', 3);
	define('TIMEOUT', 12);
require_once 'services/db.php';
require_once 'services/momo.php';

// Function used to store id and generated a unique id for that id
function set_id($id){
	$d = uniqid();
	$_SESSION['ids'][$d] = $id;
	return $d;
}

// Function used to get id by the generated id
function get_id($d){
    if(!isset($_SESSION['ids'][$d])) {
        game_log('HACKING_ATTEMPT', $d);
    }
	$result = $_SESSION['ids'][$d];
	unset($_SESSION['ids'][$d]);
	return $result;
}

function make_data($data, $value='title', $key='id'){
	$result = [];
	foreach($data as $val){
		$result[set_id($val[$key])] = $val[$value];
	}
	return $result;
}

function init_game(){
    $existing = query_rows('SELECT id FROM game WHERE languageid = ? AND investmentid = ? AND categoryid = ? AND userid = ? AND amount_invested = ? AND useremail = ? AND state IS NULL', [$_SESSION['languageid'], $_SESSION['investmentid'], $_SESSION['categoryid'], $_SESSION['userid'], query_row('SELECT amount FROM investment WHERE id = ?', [$_SESSION['investmentid']])['amount'], $_SESSION['useremail']]);
    game_log('FROM_PREVIOUS', $existing);
    if(count($existing) > 0) {
        foreach($existing as $row) {
            $_SESSION['gameid'] = $row['id'];
            if(game_paid()) {
                game_log('CONTINUE_PREVIOUS');
                return;
            }
        } 
    }
	query_insert('game', [
		'languageid'=>$_SESSION['languageid'],
		'investmentid'=>$_SESSION['investmentid'],
		'categoryid'=>$_SESSION['categoryid'],
		'userid'=>$_SESSION['userid'],
		'amount_invested'=>query_row('SELECT amount FROM investment WHERE id = ?', [$_SESSION['investmentid']])['amount'],
		'useremail'=>$_SESSION['useremail'],
		]);
	$_SESSION['gameid'] = query_insert_id();
	// DEBUG
	if($_SESSION['useremail']=='0789306256') {
		query_update('game', ['payment_status'=>'SUCCESSFUL'], ['id'=>$_SESSION['gameid']]);
	}
}

function get_available_languages(){
	$languages = query_rows('SELECT * FROM language');
	return make_data($languages);
}

function set_language($d){
	$_SESSION['languageid'] = get_id($d);
}

function get_available_categories(){
	$categories = query_rows('SELECT * FROM category');
	return make_data($categories);
}

function set_category($d){
	$_SESSION['categoryid'] = get_id($d);
}

function get_available_investments(){
	$investments = query_rows('SELECT * FROM investment ORDER BY amount ASC');
	return make_data($investments, 'amount');
}

function set_investment($d){
	$_SESSION['investmentid'] = get_id($d);
}

function set_user($telephone){
    $telephone = trim($telephone);
	$user = query_row('SELECT * FROM user WHERE telephone = ?', [$telephone]);
	$userid = '';
	$useremail = $telephone;
	if($user) {
	    if($user['account_type'] == 'D') throw new Expection('User have been disabled');
		$userid = $user['id'];
	} else {
		//validate_phone($telephone);
		if (!preg_match('/^07[2389][0-9]{7}$/', $telephone)) {
            throw new Exception('Invalid phone');
        }
		query_insert('user', ['telephone'=>$telephone]);
		$userid = query_insert_id();
		
	}
	$_SESSION['userid'] = $userid;
	$_SESSION['useremail'] = $useremail;
}

function update_level(){
	$totalresponse = query_row('SELECT COUNT(*) total FROM game_response WHERE gameid = ?', [$_SESSION['gameid']])['total'];
	
	$level = (($totalresponse - ($totalresponse % LEVEL)) / 3) + ($totalresponse % LEVEL==0 ? 0:1);
	$totalamount = query_row('SELECT SUM(amount_compensated) total FROM game_response WHERE gameid = ?', [$_SESSION['gameid']])['total'];
	query_update('game', ['level'=>$level, 'questions_answered'=>$totalresponse, 'current_compensation'=>$totalamount, 'state'=>'PLAYING'] , ['id'=>$_SESSION['gameid']]);
	
	$lost = has_row('SELECT id FROM game_response WHERE gameid = ? AND state IN ("LOST", "TIMEOUT")', [$_SESSION['gameid']]);
	if($lost) {
		query_update('game', ['state'=>'LOST'] , ['id'=>$_SESSION['gameid']]);
	} 
	
	
	if(!$lost){
	$total_answered = query_row('SELECT count(*) total FROM game_response WHERE gameid = ? AND state = "CORRECT"' , [$_SESSION['gameid']])['total'];
	
	if($total_answered%LEVEL == 0 && $totalresponse == $total_answered) {
		query_update('game', ['state'=>'CANWITHDRAW'] , ['id'=>$_SESSION['gameid']]);
	}}
}

function next_question(){
	$question = query_row('SELECT id FROM game_response WHERE gameid = ? AND state = "PENDING" LIMIT 1', [$_SESSION['gameid']]);
	if($question){
		$_SESSION['responseid'] = $question['id'];
		return;
	}
	$inquiry = query_row('SELECT id FROM inquiry WHERE id in (SELECT inquiryid FROM inquirycategory WHERE categoryid = ?) AND id IN (SELECT inquiryid FROM question WHERE languageid = ?) AND id NOT IN (SELECT inquiryid FROM game_response WHERE userid = ? AND inquiryid IS NOT NULL) ORDER BY RAND() LIMIT 1', [$_SESSION['categoryid'], $_SESSION['languageid'], $_SESSION['userid']]);
	
	$question = query_row('SELECT id, title FROM question WHERE languageid = ? AND inquiryid = ? ORDER BY RAND() LIMIT 1', [$_SESSION['languageid'], $inquiry['id']]);
	
	$answers = query_rows('SELECT id, title FROM answer WHERE languageid = ? AND inquiryid = ? ORDER BY RAND()', [$_SESSION['languageid'], $inquiry['id']]);
	
	query_insert('game_response', [
			'question_time'=>time(),
			'questionid'=>$question['id'],
			'gameid'=>$_SESSION['gameid'],
			'inquiryid'=>$inquiry['id'],
			'investmentid'=>$_SESSION['investmentid'],
			'userid'=>$_SESSION['userid'],
			'amount_compensated'=>0,
			'state'=>'PENDING',
		]);
	
	$_SESSION['responseid'] = query_insert_id();
	
	foreach($answers as $answer){
		query_insert('game_responseanswer', ['answerid'=>$answer['id'], 'game_responseid'=>$_SESSION['responseid']]);
	}
	
	update_level();
}

function request_payment(){
	$game = query_row('SELECT * FROM game WHERE id = ?', [$_SESSION['gameid']]);
	game_log('MOMO_REQUEST_TO_PAY');
	if($game['useremail'] == '0789306256') return;
	if(!($game['payment_status'] == 'SUCCESSFUL')) momo_request_to_pay($_SESSION['gameid'], $game['useremail'], $game['amount_invested']);
}

function waiting_payment(){
	$status = momo_check_payment($_SESSION['gameid']);
	game_log('MOMO_WAIT_STATUS', $status);
	return $status=='PENDING';
}

function game_paid(){
    $payment_status = query_row('SELECT useremail, payment_status FROM game WHERE id = ?', [$_SESSION['gameid']])['payment_status'];
    if($payment_status == 'SUCCESSFUL') {
        return true;
    }
    else if($payment_status == 'FAILED') {
        return false;
    }
    $status = momo_check_payment($_SESSION['gameid']);
	game_log('MOMO_PAYMENT_STATUS', $status);
	if($game['useremail'] == '0789306256') $status = 'SUCCESSFUL';
    query_update('game', ['payment_status'=>$status], ['id'=>$_SESSION['gameid']]);
	return $status=='SUCCESSFUL';
}

function can_withdraw(){
	return has_row('SELECT id FROM game WHERE id = ? AND state="CANWITHDRAW"', [$_SESSION['gameid']]);
}

function game_ended(){
	return has_row('SELECT id FROM game WHERE id = ? AND state IN ("LOST", "WON")', [$_SESSION['gameid']]);
}

function check_answer($answer){
	$answerid = get_id($answer);
	$game = query_row('SELECT * FROM game WHERE id = ?', [$_SESSION['gameid']]);
	$response = query_row('SELECT * FROM game_response WHERE id = ?', [$_SESSION['responseid']]);
	$correct = has_row('SELECT id FROM answer WHERE correct = 1 AND id = ? AND id IN (SELECT answerid FROM game_responseanswer WHERE game_responseid = ?)', [$answerid, $response['id']]);
	if($correct){
		query_update('game_response', [
			'answerid'=>$answerid,
			'answer_time'=>time(),
			'used_time'=>time() - $response['question_time'],
			'state'=>'CORRECT',
			'amount_compensated'=> round($game['level'] * $game['amount_invested'] * 2/3),
			'level' => $game['level'],
			], ['id'=>$response['id']]);
		update_level();
		return true;
	} else {
		query_update('game_response', [
			'answerid'=>$answerid,
			'answer_time'=>time(),
			'used_time'=>time() - $response['question_time'],
			'state'=>'WRONG',
			], ['id'=>$response['id']]);
		update_level();
		return false;
	}
	
}

function current_question(){
	$question = query_row('SELECT id, title FROM question WHERE id = (SELECT questionid FROM game_response WHERE id = ?)', [$_SESSION['responseid']]);
	$answers = query_rows('SELECT id, title FROM answer WHERE id IN (select answerid FROM game_responseanswer WHERE game_responseid = ?)', [$_SESSION['responseid']]);
	
	$permutations = [
        [0, 1, 2],
        [0, 2, 1],
        [1, 0, 2],
        [1, 2, 0],
        [2, 0, 1],
        [2, 1, 0]
    ];
    
    $newanswers = [];
    
    //error_log(json_encode($answers));
    
    $permutation = $permutations[rand(1,4)];
    foreach($permutation as $p) {
        $newanswers[] = $answers[$p];
    }
	
	return [
	    'id'=>$question['id'],
		'title'=>$question['title'],
		'answers'=>make_data($newanswers),
	];
}

function check_correct() {
	$question = query_row('SELECT title FROM question WHERE id = (SELECT questionid FROM game_response WHERE id = ?)', [$_SESSION['responseid']]);
	$answers = query_rows('SELECT id, title FROM answer WHERE correct = 1 AND id IN (select answerid FROM game_responseanswer WHERE game_responseid = ?)', [$_SESSION['responseid']]);
	$wrong = query_row('SELECT id, title FROM answer WHERE id = (select answerid FROM game_response WHERE id = ?)', [$_SESSION['responseid']]);
	return [
		'title'=>$question['title'],
		'correct'=>make_data($answers),
		'wrong'=>$wrong['title'],
	];
}

function init_withdraw() {
	$game = query_row('SELECT * FROM game WHERE id = ?', [$_SESSION['gameid']]);
	query_update('game', ['state'=>'WON', 'amount_gained'=>$game['current_compensation']], ['id'=>$game['id']]);
	momo_transfer($game['id'], $game['useremail'], $game['current_compensation']);
}

function __($string, ...$params){
	$data = $params;
	array_push($data, $string);
	return call_user_func_array('sprintf', $data);
}

function game_log($event, $other='') {
    if(is_array($other)) $other= json_encode($other);
    $gameid = NULL;
    if(isset($_SESSION['gameid'])) $gameid = $_SESSION['gameid'];
    query_insert('game_logs', ['event'=>$event, 'info'=>$other, 'gameid'=>$gameid, 'details'=>json_encode(['ip'=>$_SERVER['REMOTE_ADDR'], 'agent'=> $_SERVER['HTTP_USER_AGENT'], 'input'=>$_REQUEST]), 'ip'=>$_SERVER['REMOTE_ADDR'],'session'=>session_id()]);
}

function mark_game_as_timeout(){
	query_update('game', ['state'=>'TIMEOUT'], ['id'=>$_SESSION['gameid']]);
}

function remaining_time(){
	$question = query_row('SELECT question_time FROM game_response WHERE id = ?', [$_SESSION['responseid']]);
	$remaining = TIMEOUT - (time() - $question['question_time']);
	if(($remaining + 10) < 0) {
		mark_game_as_timeout();
		return false;
	}
	return $remaining;
}