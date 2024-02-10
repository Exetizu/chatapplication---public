<?php
    session_start();
    if(isset($_POST["nick"])  &&
    isset($_POST["email"])    &&
    isset($_POST["password"]) &&
    isset($_POST["repeat"])){
        $errors = false;
        $nick = $_POST["nick"];
        $email = $_POST["email"];
        $password = $_POST["password"];
        $repeat = $_POST["repeat"];
        ///nick
        if(strlen($nick)<5 || strlen($nick)>15){
            $errors = true;
            $_SESSION["error_value"] = "Nick nie spełnia wymagań.";
        }
        ///email
        if(!preg_match("/^[a-z\d]+[\w\d.-]*@(?:[a-z\d]+[a-z\d-]+\.){1,5}[a-z]{2,6}$/i", $email)){
            $errors = true;
            $_SESSION["error_value"] = "E-mail nie spełnia wymagań.";
        }
        ///password
        if(!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])[a-zA-Z\\d\\W]{8,20}$/i", $password)){
            $errors = true;
            $_SESSION["error_value"] = "Hasło nie spełnia wymagań.";
        }
        else{
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
        }
        ///repeat
        if($password != $repeat){
            $errors = true;
            $_SESSION["error_value"] = "Hasła nie są identyczne.";
        }
        //checkbox
        if(!isset($_POST["rules"])){
            $errors = true;
            $_SESSION["error_value"] = "Proszę zaakceptować regulamin";
        }
        //bot
        //$answer = @json_decode(file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='."6LeBig4dAAAAAHc5LGv9XdOas8-PF-u1wCFYEGah".'&response='.$_POST['g-recaptcha-response']));
        //if ($answer->success==false)
        //{
        //    $errors=true;
        //    $_SESSION["error_value"] = "Nie udana reCAPTCHA";
        //}
        if ($errors==false){
            require_once "connect.php";
            mysqli_report(MYSQLI_REPORT_STRICT);
            try{
                $connection = new mysqli($host, $db_user, $db_password, $db_name);
                if ($connection->connect_errno==0){
                    $request = $connection->query("SELECT id FROM dane_logowania WHERE email='$email'");
                    if (!$request) throw new Exception();
                    if($request->num_rows>0){
                        $errors=true;
                        $_SESSION['error_value']="Podany email jest już używany.";
                        $_SESSION["l_nick"] = $nick;
                        $_SESSION["l_email"] = $email;
                    }		
                    $request = $connection->query("SELECT id FROM dane_logowania WHERE nick='$nick'");
                    if (!$request) throw new Exception();
                    if($request->num_rows>0){
                        $errors=true;
                        $_SESSION['error_value']="Podany nick jest zajęty.";
                        $_SESSION["l_nick"] = $nick;
                        $_SESSION["l_email"] = $email;
                    }
                    if ($errors==false){
                        $unique_id = uniqid();
                        if ($connection->query("INSERT INTO dane_logowania VALUES ('', '$nick', '$email', '$password_hash', '$unique_id')")){
                            header("Location: /register/thanks");
                        }
                        else{
                            throw new Exception();
                        }
                    }
                    $connection->close();
                }
            }
            catch(Exception $e)
            {
                $_SESSION['error_value']="Błąd połączenia z serwerem.";
            }
        }
        else{
            $_SESSION["l_nick"] = $nick;
            $_SESSION["l_email"] = $email;
            unset( $_SESSION["nick"]);
            unset( $_SESSION["email"]);
            unset( $_SESSION["password"]);
            unset( $_SESSION["repeat"]);
            $errors = false;
        }
    }
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet"  href="https://fonts.googleapis.com/css?family=Open+Sans">
    <link rel="stylesheet"  href="/fonts/et-line-font/style.css">
    <link rel="icon" href="/logo.png">
    <script src="https://www.google.com/recaptcha/api.js"></script>
    <title>Rejestracja || Chat application</title>
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
        height: 660px;
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
            <div id="form_title">Rejestracja</div>
            <form method="post" id="form"> 
                    <?php
                        if(isset($_SESSION["error_value"])){
                            if($_SESSION["error_value"]=="Podany nick jest zajęty."){
                                unset($_SESSION["l_nick"]);
                            }
                            else if($_SESSION["error_value"]=="Podany email jest już używany."){
                                unset($_SESSION["l_email"]);
                            }
                            
                        }
                    ?>
                    Nick
                <div class="form-text-block">
                    <div class="form-icon" data-icon="&#xe08a;"></div>
                    <input class="form-text" autocomplete="nickname" required type="text"  placeholder="Nick" name="nick" value="<?php if(isset($_SESSION["l_nick"])){echo $_SESSION["l_nick"]; unset($_SESSION["l_nick"]);}?>">
                    <div class="form-requirements-icon" data-icon="&#xe060;">
                        <div class="form-requirements-box">
                           <div class="form-requirements-line"> *od 5 do 15 znaków</div>
                        </div>
                    </div>
                </div>
                    E-mail
                    <div class="form-text-block">
                        <div class="form-icon" data-icon="&#xe076;"></div>
                        <input class="form-text" style="padding-right: 12px;" required autocomplete="email" type="email" placeholder="E-mail" name="email" value="<?php if(isset($_SESSION["l_email"])){echo $_SESSION["l_email"]; unset($_SESSION["l_email"]);}?>">
                    </div>   
                    <script>
                            <?php
                                if(isset($_SESSION["error_value"])){
                                    if($_SESSION["error_value"]=="Podany nick jest zajęty."){
                                        echo "document.forms[0].nick.style.borderColor = 'red';";
                                        echo "document.forms[0].email.style.borderColor = 'rgb(38, 165, 34)';";
                                    }
                                    if($_SESSION["error_value"]=="Podany email jest już używany."){
                                        echo "document.forms[0].email.style.borderColor = 'red';";
                                    }
                                }
                            ?>
                        </script> 
                    Hasło
                <div class="form-text-block">
                    <div class="form-icon" data-icon="&#xe06c;"></div>
                    <input class="form-text" type="password" autocomplete="new-password" required placeholder="Hasło" name="password">
                    <div class="form-requirements-icon" data-icon="&#xe060;">
                        <div class="form-requirements-box">
                            <div class="form-requirements-line">*od 8 do 20 znaków</div><br>
                            <div class="form-requirements-line">*minimum 1 duża litera</div><br>
                            <div class="form-requirements-line">*minimum 1 mała litera</div><br>
                            <div class="form-requirements-line">*minimum 1 liczba</div>
                        </div> 
                    </div>
                </div>
                   
                    Powtórz hasło
                <div class="form-text-block">
                    <div class="form-icon" data-icon="&#xe06c"></div>
                    <input class="form-text" required style="padding-right: 12px;" autocomplete="new-password" type="password" placeholder="Powtórz hasło" name="repeat">
                </div>
                <label id="form-checkbox-label"><input type="checkbox" id="form-checkbox" name="rules">Akceptuję regulamin</label>
                <div class="g-recaptcha" data-sitekey="6LeBig4dAAAAAPQU_nplP9FUOqhKKiLj3EguI8x2"></div>
                <button id="form-submit">Zarejestruj</button>
                <div id="form-error"><?php if(isset($_SESSION["error_value"])){echo $_SESSION["error_value"];}?></div>
            </form>
        </div>
    </main>
    <script>

        var errors = [true,true,true,true,true]
        <?php
            if(isset($_SESSION["error_value"])){
                if($_SESSION["error_value"]=="Podany nick jest zajęty."){
                    echo "errors[1] = false;";
                }
                unset($_SESSION["error_value"]);
            }
        ?>
        
        /////nick
        document.forms[0].nick.addEventListener("focusin",()=>{
            document.getElementsByClassName("form-requirements-box")[0].style.display = ""
        })
        document.forms[0].nick.addEventListener("focusout",(e)=>{
            if(document.forms[0].nick.value.length<5 || document.forms[0].nick.value.length>15 ){
                document.forms[0].nick.style.borderColor = "red"
                errors[0] = true
                document.getElementsByClassName("form-requirements-box")[0].getElementsByClassName("form-requirements-line")[0].style.color = "red"
            }
            else{
                errors[0] = false
            }

        })
        document.forms[0].nick.addEventListener("input",()=>{
            if(document.forms[0].nick.value.length>=5 && document.forms[0].nick.value.length<=15){
                document.forms[0].nick.style.borderColor = "rgb(38, 165, 34)"
                document.getElementsByClassName("form-requirements-box")[0].getElementsByClassName("form-requirements-line")[0].style.color = "rgb(38, 165, 34)"
                document.getElementsByClassName("form-requirements-icon")[0].style.color = "rgb(38, 165, 34)"
            }
            else{
                document.forms[0].nick.style.borderColor = "rgb(118, 118, 118)"
                document.getElementsByClassName("form-requirements-box")[0].getElementsByClassName("form-requirements-line")[0].style.color = "rgb(118, 118, 118)"
                document.getElementsByClassName("form-requirements-icon")[0].style.color = "black"
            }
        })
        /////email
        document.forms[0].email.addEventListener("focusout",()=>{
            const re = /^[a-z\d]+[\w\d.-]*@(?:[a-z\d]+[a-z\d-]+\.){1,5}[a-z]{2,6}$/i;
            if(!re.test(document.forms[0].email.value.trim())){
                document.forms[0].email.style.borderColor = "red"
                errors[1] = true
            }
            else{
                errors[1] = false
            }
        })
        document.forms[0].email.addEventListener("input",()=>{
            const re = /^[a-z\d]+[\w\d.-]*@(?:[a-z\d]+[a-z\d-]+\.){1,5}[a-z]{2,6}$/i;
            if(re.test(document.forms[0].email.value)){
                document.forms[0].email.style.borderColor = "rgb(38, 165, 34)"
            }
            else{
                document.forms[0].email.style.borderColor = "rgb(118, 118, 118)"
            }
        })
        /////password
        document.forms[0].password.addEventListener("focusin",()=>{
            document.getElementsByClassName("form-requirements-box")[1].style.display = ""
        })
        document.forms[0].password.addEventListener("focusout",()=>{
            const re = new RegExp("^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])[a-zA-Z\\d\\W]{8,20}$");
            if(!re.test(document.forms[0].password.value)){
                document.forms[0].password.style.borderColor = "red"
                errors[2] = true
                if(!new RegExp("[a-zA-Z\\d\\W]{8,20}$").test(document.forms[0].password.value)){
                    document.getElementsByClassName("form-requirements-box")[1].getElementsByClassName("form-requirements-line")[0].style.color = "red"
                }
                if(!new RegExp("(?=.*[A-Z])").test(document.forms[0].password.value)){
                    document.getElementsByClassName("form-requirements-box")[1].getElementsByClassName("form-requirements-line")[1].style.color = "red"
                }
                if(!new RegExp("(?=.*[a-z])").test(document.forms[0].password.value)){
                    document.getElementsByClassName("form-requirements-box")[1].getElementsByClassName("form-requirements-line")[2].style.color = "red"
                }
                if(!new RegExp("(?=.*[0-9])").test(document.forms[0].password.value)){
                    document.getElementsByClassName("form-requirements-box")[1].getElementsByClassName("form-requirements-line")[3].style.color = "red"
                }
            }
            else{
                errors[2] = false
            }
            
        })
        document.forms[0].password.addEventListener("input",()=>{
            const re = new RegExp("^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])[a-zA-Z\\d\\W]{8,20}$");
            if(re.test(document.forms[0].password.value)){
                document.forms[0].password.style.borderColor = "rgb(38, 165, 34)"
                document.getElementsByClassName("form-requirements-icon")[1].style.color = "rgb(38, 165, 34)"
            }
            else{
                document.forms[0].password.style.borderColor = "rgb(118, 118, 118)"
                document.getElementsByClassName("form-requirements-icon")[1].style.color = "black"
            }
            if(new RegExp("[a-zA-Z\\d\\W]{8,20}$").test(document.forms[0].password.value)){
                    document.getElementsByClassName("form-requirements-box")[1].getElementsByClassName("form-requirements-line")[0].style.color = "rgb(38, 165, 34)"
                }
                else{
                    document.getElementsByClassName("form-requirements-box")[1].getElementsByClassName("form-requirements-line")[0].style.color = "rgb(118, 118, 118)"
                }
                if(new RegExp("(?=.*[A-Z])").test(document.forms[0].password.value)){
                    document.getElementsByClassName("form-requirements-box")[1].getElementsByClassName("form-requirements-line")[1].style.color = "rgb(38, 165, 34)"
                }
                else{
                    document.getElementsByClassName("form-requirements-box")[1].getElementsByClassName("form-requirements-line")[1].style.color = "rgb(118, 118, 118)"
                }
                if(new RegExp("(?=.*[a-z])").test(document.forms[0].password.value)){
                    document.getElementsByClassName("form-requirements-box")[1].getElementsByClassName("form-requirements-line")[2].style.color = "rgb(38, 165, 34)"
                }
                else{
                    document.getElementsByClassName("form-requirements-box")[1].getElementsByClassName("form-requirements-line")[2].style.color = "rgb(118, 118, 118)"
                }
                if(new RegExp("(?=.*[0-9])").test(document.forms[0].password.value)){
                    document.getElementsByClassName("form-requirements-box")[1].getElementsByClassName("form-requirements-line")[3].style.color = "rgb(38, 165, 34)"
                }
                else{
                    document.getElementsByClassName("form-requirements-box")[1].getElementsByClassName("form-requirements-line")[3].style.color = "rgb(118, 118, 118)"
                }
            const re1 = new RegExp("^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])[a-zA-Z\\d\\W]{8,20}$");
            if(re1.test(document.forms[0].password.value) && document.forms[0].repeat.value == document.forms[0].password.value){
                document.forms[0].repeat.style.borderColor = "rgb(38, 165, 34)"
            }
            else{
                document.forms[0].repeat.style.borderColor = "rgb(118, 118, 118)"
            }
        })
        /////password repeat
        document.forms[0].repeat.addEventListener("focusout",()=>{
            const re = new RegExp("^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])[a-zA-Z\\d\\W]{8,20}$");
            if(!re.test(document.forms[0].password.value) || (document.forms[0].repeat.value != document.forms[0].password.value && document.forms[0].password.value != 0)){
                document.forms[0].repeat.style.borderColor = "red"
                errors[3] = true
            }
            else{
                errors[3] = false
            }
        })
        document.forms[0].repeat.addEventListener("input",()=>{
            const re = new RegExp("^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])[a-zA-Z\\d\\W]{8,20}$");
            if(re.test(document.forms[0].password.value) && document.forms[0].repeat.value == document.forms[0].password.value){
                document.forms[0].repeat.style.borderColor = "rgb(38, 165, 34)"
            }
            else{
                document.forms[0].repeat.style.borderColor = "rgb(118, 118, 118)"
            }
        })
        /////checkbox
        document.getElementById("form-checkbox-label").addEventListener("click",()=>{
            if(!document.getElementById("form-checkbox").checked){
                errors[4] = true
            }
            else{
                document.getElementById("form-checkbox-label").style.border = "none"
                document.getElementById("form-checkbox-label").style.height = "26px"
                document.getElementById("form-checkbox-label").style.top = "0px"
                errors[4] = false
            }
        })
        /////submit form
        document.getElementById("form-submit").addEventListener("click",(e)=>{
            e.preventDefault()
            if(!errors[0] && !errors[1] && !errors[2] && !errors[3] && !errors[4] && document.forms[0].nick.value != "" && document.forms[0].email.value != "" && document.forms[0].password.value != "" && document.forms[0].repeat.value != ""){
                document.forms[0].submit()
            }
            else{
                document.getElementById("form-error").innerHTML = "Wprowadź prawidłowe dane."
                errors.forEach((element, index, array) => {
                    if(index == 4){
                        if(element){
                            document.getElementById("form-checkbox-label").style.border = "2px solid red"
                            document.getElementById("form-checkbox-label").style.height = "22px"
                            document.getElementById("form-checkbox-label").style.top = "-2px"
                        }
                    }
                    else if(element){
                        document.forms[0].getElementsByTagName("input")[index].style.borderColor = "red"
                        if(index ==0){
                            document.getElementsByClassName("form-requirements-box")[0].style.display = "flex"
                            document.getElementsByClassName("form-requirements-box")[0].getElementsByClassName("form-requirements-line")[0].style.color = "red"
                        }
                        if(index ==2){
                            document.getElementsByClassName("form-requirements-box")[1].style.display = "flex"
                            if(!new RegExp("[a-zA-Z\\d\\W]{8,20}$").test(document.forms[0].password.value)){
                                document.getElementsByClassName("form-requirements-box")[1].getElementsByClassName("form-requirements-line")[0].style.color = "red"
                            }
                            if(!new RegExp("(?=.*[A-Z])").test(document.forms[0].password.value)){
                                document.getElementsByClassName("form-requirements-box")[1].getElementsByClassName("form-requirements-line")[1].style.color = "red"
                            }
                            if(!new RegExp("(?=.*[a-z])").test(document.forms[0].password.value)){
                                document.getElementsByClassName("form-requirements-box")[1].getElementsByClassName("form-requirements-line")[2].style.color = "red"
                            }
                            if(!new RegExp("(?=.*[0-9])").test(document.forms[0].password.value)){
                                document.getElementsByClassName("form-requirements-box")[1].getElementsByClassName("form-requirements-line")[3].style.color = "red"
                            }
                        }
                    }
                    
                });
            }
        })
        
    </script>
</body>
</html>