<?php
	session_start();
	if(isset( $_SESSION["email"]) && isset( $_SESSION["unique_id"])){
        $email = $_SESSION["email"];
        $unique_id = $_SESSION["unique_id"];
        require_once "connect.php";
        $connection = @new mysqli($host, $db_user, $db_password, $db_name);
        if ($connection->connect_errno==0){
            $request = $connection->query("SELECT * FROM dane_logowania WHERE email='$email' AND unique_id='$unique_id'");
            if($request->num_rows==0){
                header('Location: /login');
            }
            else{
                $line = $request->fetch_assoc();
                echo "Witaj:<b>".$line['nick']."</b>!";
            }
        }
		$connection->close();
    }
    else{
        header('Location: /login');
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<button id="logout">Wyloguj</button>
    <script>
        document.getElementById("logout").addEventListener("click",()=>{
            var date = new Date();
            document.cookie = "unique_id = ; expires=" + date.toGMTString() + "; path=/login";
            document.cookie = "email = ; expires=" + date.toGMTString() + "; path=/login";
            document.location.href = "/login";
        })
    </script>
</body>
</html>