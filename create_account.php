<?php
function random_str($length, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')
{
    $str = '';
    $max = mb_strlen($keyspace, '8bit') - 1;
    for ($i = 0; $i < $length; ++$i) {
        $str .= $keyspace[rand(0, $max)];
    }
    return $str;
}

//shamelessly stolen from stackoverflow. no idea whats happening here
function prompt_silent($prompt = "Enter Password:") {
  if (preg_match('/^win/i', PHP_OS)) {
    $vbscript = sys_get_temp_dir() . 'prompt_password.vbs';
    file_put_contents(
      $vbscript, 'wscript.echo(InputBox("'
      . addslashes($prompt)
      . '", "", "password here"))');
    $command = "cscript //nologo " . escapeshellarg($vbscript);
    $password = rtrim(shell_exec($command));
    unlink($vbscript);
    return $password;
  } else {
    $command = "/usr/bin/env bash -c 'echo OK'";
    if (rtrim(shell_exec($command)) !== 'OK') {
      trigger_error("Can't invoke bash");
      return;
    }
    $command = "/usr/bin/env bash -c 'read -s -p \""
      . addslashes($prompt)
      . "\" mypassword && echo \$mypassword'";
    $password = rtrim(shell_exec($command));
    echo "\n";
    return $password;
  }
}

echo "Create Account\n";

$username = readline("Enter desired username: ");

$p1 = "";
$p2 = "";
do {
	if($p1 !== $p2) {
		echo "Passwords don't match, try again.\n";
	}

	$p1 = prompt_silent();
	$p2 = prompt_silent($prompt = "Re-enter: ");
}while($p1 !== $p2);

$hash = password_hash($p1, PASSWORD_BCRYPT);

$email = readline("Enter email: ");
$is_ucsb = readline("Do you go to UCSB? (Y/N) ");
if($is_ucsb === "Y" || $is_ucsb === "y") {
	$school = "UCSB";
}
else {
	$school = readline("Enter your school abbreviation: ");
}

$id = random_str(16);

echo "\nMySQL code follows for inserting a new user:\n\n";

echo "INSERT INTO users VALUES('$id', '$username', '$email', '$hash', '$school');\n\n";

?>
