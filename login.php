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
$credential = $db->real_escape_string(str_replace(" ", "",$_REQUEST["credential"])); //replace all spaces
$password = $db->real_escape_string($_REQUEST['password']);

$credentialType = "email";
if(strpos($credential, '@') === false) {
 //if $credential does NOT contain an @ symbol, change $credentialType to username
 $credentialType = "username";
}

//"select password from users where email like 'test@test.com'
//         OR
//"select password from users where username like 'testuser'
$sql = "select password from users where $credentialType like '$credential' limit 1"; //query just the password at first
if(!$result = $db->query($sql)){
  die('There was an error checking password for user');
}

if($result == NULL || mysql_num_rows($result) == 0) { //no password found for user (user does not exist)
    die('No user found where ' . $credentialType . ' = ' . $credential);
}
else if($row=$result->fetch_assoc()) {
    if(!password_verify($password,$row["password"])) { //user exists, invalid password
        die('Incorrect password');
    }
}


//user exists and valid password, send data types back to application for user
$sql = "select userID, username, schoolName from users where $credentialType like '$credential'";
if(!$result = $db->query($sql)) {
    die('There was an error running the query: \"'. $str . '\" ------- Error: \"' . $db->error .'\"');
}

$returnValues = array(); //create array to pass back to application
if($result == NULL || mysql_num_rows($result) == 0) { //something funky happened
    $returnValues["success"] = false;
    $returnValues["message"] = "There were no entries for this user";
    $returnValues["id"] = NULL;
}
else if($row = $result->fetch_assoc()) { //return user information with email + password combo (again, charlie, is this safe?)
    $returnValues["success"] = true;
    $returnValues["id"] = $row["userID"];
    $returnValues["username"] = $row["username"];
    $returnValues["email"] = $row["email"];
    $returnValues["schoolName"] = $row["schoolName"];
}
else { //again, something funky happened
    $returnValues["success"] = false;
    $returnValues["message"] = "There were no entries for this user";
    $returnValues["id"] = NULL;
}

echo json_encode($returnValues); //return json data back to user on failure or success, only if user+password match up

?>