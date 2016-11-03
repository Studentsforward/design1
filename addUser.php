<?php

$access = $_REQUEST["access"]; //possible password sent via POST to protect our files from xss
if(true) {// ($access === "someSuperSecretPassword") {
    $mysqlUsername = "some_user";
    $mysqlPassword = "some_password"
    $databaseName = "some_database"
	$db = new mysqli("localhost", "$mysqlUsername", "$mysqlPassword", "$databaseName");
    if ($db->connect_errno) {
        echo "Failed to connect to MySQL: (" . $db->connect_errno . ") " . $db->connect_error;
       die(': Exiting script');
    }
}
else { 
	die('Invalid access');
}



//$db->real_escape_string prevents sql injection (it adds a '\' before all single and double quotes)
$username = db->real_escape_string(str_replace("@","",str_replace(" ", "",$_REQUEST["username"]))); //remove all @ and spaces
$email = $db->real_escape_string($_REQUEST['email']);
$password = $db->real_escape_string($_REQUEST['password']);
$school = $db->real_escape_string($_REQUEST['school']);
$passwordHash = password_hash($password, PASSWORD_BCRYPT);

if($school == NULL || $school == "") {
    $school = "none";
}

//generate 16 character userID
include('randomKey.php');

$valid_id = false; //used to determine if the id generated already exists


/*** This while loop below, which generates a random 16-character string, may seem inefficient, but 
**** there are 16! (factorial) possible IDs to be created. The chances of this loop taking more than 
**** 3 times  is extremely insignificant. If anybody else has a better way of doing this, please let 
**** the dev team know
*/
do {
    $id = random_str(16);
    $sql = "select userID from users where userID='$id'";
  
    if(!$result = $db->query($sql)) {
        echo "There was an error running the query: \"". $str . "\" ------- Error: \"" . $db->error ."\"";
    }
    
    //check if query is NULL or does not return a row. 
    //if it does return a row, this ID is taken. stay in loop until valid ID is generated
    if($result == NULL || mysql_num_rows($result) == 0) {
        $valid_id = true;
    }

} while(!$valid_id);


$sql = "insert into users values('$id','$username','$email','$passwordHash','$school')";
if(!$result = $db->query($sql)) {
    echo "There was an error running the query: \"". $str . "\" ------- Error: \"" . $db->error ."\"";
}

$arr = array();
if($result == NULL || mysql_num_rows($result) == 0) {
    $arr["success"] = true;
    $arr["id"] = $id; //return generated ID back to user via JSON (charlie, is this safe?)
}
else {
    $arr["success"] = false;
    $arr["id"] = NULL;
}

echo json_encode($arr); //return json data back to user on failure or success


?>
