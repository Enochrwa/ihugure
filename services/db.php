<?php
define('DEBUG',0);

$db_username = 'ihugure';
$db_password = '@Rwanda1';
$db_name = 'ihugure';
$db_hostname = 'localhost';
function display_error($error, $level=0){
	error_log($error);
	echo $error;
	exit(0);
}
try{
$db = new PDO("mysql:host=$db_hostname;dbname=$db_name", $db_username, $db_password);
$db->setAttribute(PDO::ATTR_AUTOCOMMIT, 1);
$stmt = NULL;

function query_rows($sql, $params=[]){
	global $db,$stmt;
	global $db,$stmt;
	$stmt = $db->prepare($sql);
	if($stmt===false) display_error("Query Error", 1);
	$stmt->execute($params);
	if(!$stmt->execute($params)){
		if(DEBUG)print_r($stmt->errorInfo());
		display_error('Database Error');
	}
	return $stmt->fetchall();
}

function query($sql, $params=[]){
	global $db,$stmt;
	$stmt = $db->prepare($sql);
	if($stmt===false) display_error("Query Error", 1);
	if(!$stmt->execute($params)){
		if(DEBUG)print_r($stmt->errorInfo());
		display_error('Database Error');
	}
}

function query_row($sql, $params=[]){
	global $db,$stmt;
	return @query_rows($sql, $params)[0];
}

function has_row($sql, $params=[]){
	global $db,$stmt;
	if(count(query_rows($sql, $params))>0) return true;
	else false;
}

function query_insert($table_name, $values){
	$columns = array_keys($values);
	$values_holder = array_fill(0, count($columns), "?");
	$columns = implode(",",$columns);
	$values_holder = implode(",",$values_holder);
	$params = array_values($values);
	$sql = "INSERT INTO $table_name($columns) VALUES($values_holder)";
	//echo $sql;
	//print_r($params);
	return query($sql, $params);
}
function query_update($table_name, $values, $where = '1'){
	$columns = array_keys($values);
	$columns = array_map(function($val){return "$val=?";}, $columns);
	$columns = implode(",",$columns);
	$params = array_values($values);
	if(is_array($where)){
		$params = array_merge($params, array_values($where));
		$where = array_keys($where);
		$where = array_map(function($val){return "$val=?";}, $where);
		$where = implode(" AND ", $where);
	}
	$sql = "UPDATE $table_name SET $columns WHERE $where";
	return query($sql, $params);
}

function query_insert_id(){
    global $db;
    return $db->lastInsertId();
}

function migrate($name, $callback, $query=true){
    query("CREATE TABLE IF NOT EXISTS migration(
    id int auto_increment,
    name varchar(255),
    checksum varchar(255),
    addtime timestamp default current_timestamp,
    primary key(id)
);");
    if(!has_row("SELECT * FROM migration WHERE name=?", [$name])) {
    if($query){
        query($callback);
        query_insert('migration', ['name'=>$name, 'checksum'=>md5($callback)]);
    } else {
        call_user_func($callback);
        query_insert('migration', ['name'=>$name]);
    }
    }
}

}catch(Exception $e){
	display_error("Connection to dabase failed");
}

