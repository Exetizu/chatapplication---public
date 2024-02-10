<?php
	session_start();
	if(isset($_COOKIE["email"]) && isset($_COOKIE["unique_id"])){
        $email = $_COOKIE["email"];
        $unique_id = $_COOKIE['unique_id'];
        require_once "connect.php";
        $connection = @new mysqli($host, $db_user, $db_password, $db_name);
        if ($connection->connect_errno==0){
            $request = $connection->query("SELECT id FROM dane_logowania WHERE email='$email' AND unique_id='$unique_id'");
            if($request->num_rows>0){
                $_SESSION["email"] = $_COOKIE["email"];
                $_SESSION["unique_id"] = $_COOKIE["unique_id"];
                header('Location: /home');
                $connection->close();
                exit;
            }
        }
		$connection->close();
        unset($email);
        unset($password);
    }
    else{
        if(isset($_SESSION["email"]) && isset($_SESSION["unique_id"])){
            unset($_SESSION["email"]);
            unset($_SESSION["unique_id"]);

        }
    }
	if (isset($_POST['email']) && isset($_POST['password']))
	{
        $email = $_POST['email'];
        $password = $_POST['password'];
        require_once "connect.php";
        $connection = @new mysqli($host, $db_user, $db_password, $db_name);
        if ($connection->connect_errno==0){
            $email = htmlentities($email, ENT_QUOTES, "UTF-8");
            if ($request = @$connection->query(
            sprintf("SELECT * FROM dane_logowania WHERE email='%s'",
            mysqli_real_escape_string($connection,$email)))){
                if($request->num_rows>0){
                    $line = $request->fetch_assoc();
                    if (password_verify($password, $line['password'])){
                        $unique_id = uniqid();
                        $_SESSION["email"] = $email;
                        $_SESSION["unique_id"] = $unique_id;
                        setrawcookie("unique_id", $unique_id);
                        setrawcookie("email", $line['email']);
                        $request = @$connection->query(
                            sprintf("UPDATE dane_logowania SET unique_id='$unique_id' WHERE email='%s';",
                            mysqli_real_escape_string($connection,$email)));
                        header('Location: /home');
                    }
                    else{
                        $_SESSION["error_value"] = "Wprowadzono błędny login lub hasło.";
                    }
                }
                else{
                    $_SESSION["error_value"] = "Wprowadzono błędny login lub hasło.";
                }
            }
            else{
                $_SESSION["error_value"] = "Wprowadzono błędny login lub hasło.";
            }
        }
        else{
            $_SESSION["error_value"] = "Błąd połączenia z serwerem.";
        }
		$connection->close();
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet"  href="https://fonts.googleapis.com/css?family=Open+Sans">
    <link rel="stylesheet"  href="/fonts/et-line-font/style.css">
    <link rel="icon" href="/logo.png">
    <title>Logowanie || Chat application</title>
<style>
    :root{
        --shadow-color:rgb(146, 146, 146);
    }
    body{
        background-color: rgb(247, 247, 247);
        height: 100vh;
        width: 100vw;
        margin: 0;
        font-family: Open Sans;
        overflow-x: hidden;
    }
    header{
        background-color: rgb(255, 255, 255);
        height: 50px;
        width: 100%;
        min-width: 350px;
        box-shadow: 0 5px 15px var(--shadow-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    #header-title{
        margin-right: 10px;
    }
    #header-left{
        display: flex;
        align-items: center;
        display: flex;
        flex-direction: row;
        height: 50px;
    }
    #header-logo{
        margin: 5px;
        margin-right: 20px;
        height: calc(100% - 10px);
    }
    main{
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 780px;
        height: calc(100vh - 50px);
        width: 100vw;
        min-width: 350px;
    }
    #main{
        width: 400px;
        height: 400px;
        background-color: rgb(255, 255, 255);
        box-shadow: 0 5px 40px var(--shadow-color),
                    0 0 15px var(--shadow-color);
        border-radius: 10px;
        display: flex;
        align-items: center;
        flex-direction: column;
        margin: 15px;
        margin-top: -50px;
    }
    form{
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 100%;
        height: 100%;
    }
    #form_title{
        margin-top: 40px;
        margin-bottom: 40px;
        font-weight: 600;
        font-size: 20px;
    }
    .form-text{
        border-radius: 0;
        height: 30px;
        width: 100%;
        padding-left: 32px;
        padding-right: 28px;
        margin-top: 5px;
        border: 2px solid rgb(118, 118, 118);
    }
    .form-text-block{
        width: 80%;
        display:flex;
        flex-direction:row;
        align-items: center;
        margin-bottom: 10px;
    }
    .form-icon{
        width: 0px; 
        z-index: 2;
        position: relative; 
        left: 10px;
        margin-top: 5px;
    }
    .form-requirements-box{
        font-size: 12px;
        margin-bottom: 10px;
        color: gray;
        box-shadow: 0 0 10px black;
        border-radius: 10px;
        min-width: 140px;
        background-color: rgb(247, 247, 247);
        position: absolute;
        display: none;
        padding: 10px;
        z-index: 3;
        flex-direction: column;
        align-items: flex-start;
        justify-content: center;
    }
    .form-requirements-icon{
        width: 0px; 
        z-index: 1;
        position: relative; 
        right: 10px;
        margin-top: 5px;
        display: flex;
        justify-content: flex-end;
        
    }
    .form-requirements-icon:hover{
        font-size: 1.2em;
    }
    .form-requirements-icon:hover  .form-requirements-box{
        display: flex;
    }
    .form-requirements-line{
        margin-top: -7px;
        margin-bottom: -7px;
    }
    .form-text:focus{
        border-radius: 0;
        outline: none;
    }
    #form-submit{
        margin-bottom: 10px;
        font-size: 18px;
        width: calc(80% + 8px);
        margin-top: 30px;
        background-color: #01a1e6;
        height: 40px;
        border-radius: 50px;
        border: none;
        transition: background .2s;
    }
    #form-submit:hover{
        background-color: #008ECC;
    }
    #form-error{
        text-align: center;
        color: rgb(255, 0, 0);
        font-weight:600;
        width: 80%;
        height: 26px;
    }
    #form-checkbox-label{
        margin-bottom: 20px;
        height: 26px;
        position: relative;
    }
</style>
</head>
<body>
    <header>
        <div id="header-left">
            <img src="/logo.png" id="header-logo" alt="logo" height="">
            <div id="header-title">Chat application</div>
        </div>
    </header>
    <main>
        <div id="main">
            <div id="form_title">Logowanie</div>
            <form method="post" id="form"> 
                    E-mail
                <div class="form-text-block">
                    <div class="form-icon" data-icon="&#xe076;"></div>
                    <input class="form-text" require autocomplete="email" type="email" placeholder="E-mail" name="email">
                </div>    
                    Hasło
                <div class="form-text-block">
                    <div class="form-icon" data-icon="&#xe06c;"></div>
                    <input class="form-text" require autocomplete="current-password" type="password" placeholder="Hasło" name="password">
                </div>
                <button id="form-submit">Zaloguj</button>
                <div id="form-error"><?php if(isset($_SESSION["error_value"])){echo $_SESSION["error_value"]; unset($_SESSION["error_value"]);}?></div>
            </form>

        </div>
    </main>
    <script>
        /////submit form
        document.getElementById("form-submit").addEventListener("click",(e)=>{
            e.preventDefault()
            document.forms[0].submit()
        })
        
    </script>
</body>
</html>