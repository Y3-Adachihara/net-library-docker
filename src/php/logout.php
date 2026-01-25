<?php
    require_once '../db_connect.php';
    session_start();

    $index = $_POST['page_id'] ?? 0;
    $path_array = [
        0 => '../html/student_login.php',
        1 => '../html/librarian_login.php'
    ];
    $redirect_path = $path_array[$index] ?? '../html/student_login.php';
    
    $error_param = "";
    if(!isset($_POST['csrf_token']) || $_POST['csrf_token'] != $_SESSION['csrf_token']){
        $error_param = "?error=csrf_alert";
    }
    
    $_SESSION = [];    // セッション変数を全て解除
    
    // セッションクッキーの削除
    if(ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();  // セッションを破壊

    header("Location: " .$redirect_path .$error_param);
    exit();
?>