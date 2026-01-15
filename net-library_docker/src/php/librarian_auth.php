<?php 
    require_once '../db_connect.php';
    session_start();

    $school_id = $_POST["school"];
    $user_id = $_POST["login_id"];
    $password = $_POST['password'];

    //CSRF対策
    //トークンが一致しないか（右）、そもそもトークンがlogin.php(ログイン画面)から送られていない（左）時
    if (!isset($_POST["csrf_token"]) || $_POST["csrf_token"] != $_SESSION["csrf_token"]) {
        $_SESSION['error'] = "CSFS対策に引っかかりました（開発者向けエラーメッセージ）";    //本番はメッセージの内容を変える
        header("Location: ../html/librarian_login.php");
        exit(); 
    }

    try {
        $db = new db_connect();
        $db->connect(); //データベースへ接続

        $stmt_select = $db->pdo->prepare("SELECT librarian_id, password FROM librarian WHERE school_id = :school_id AND login_id = :login_id");
        
        $stmt_select->bindValue(':school_id', $school_id, PDO::PARAM_INT);
        $stmt_select->bindValue(':login_id', $user_id, PDO::PARAM_STR);
        
        $stmt_select->execute();
        $row= $stmt_select->fetch(PDO::FETCH_ASSOC);


        //パスワードが取得できており、かつPOSTでパスワードが送られてきている場合
        if ($row != false && isset($_POST['password'])) {
            $passwd_hash = $row['password'];
            
            //パスワードを比較
            if(password_verify($_POST['password'], $passwd_hash)) {

                //セッション固定化攻撃対策
                session_regenerate_id(true);

                //ここでのlibrarian_idは先のSELECT文で取得した司書ID
                $_SESSION['librarian_id'] = $row['librarian_id'];
                header("Location: ../html/librarian_myPage.php");
                exit();

            } else {
	            $_SESSION['error'] = "IDまたはパスワードが違います。";
                header("Location: ../html/librarian_login.php");
                exit();
            }
        } else {
            $_SESSION['error'] = "パスワードの取得に失敗しました。";
            header("Location: ../html/librarian_login.php");
            exit();
        }
        

    } catch (PDOException $e) {
        echo "データベースエラー：" . $e->getMessage(); //デバッグ用。あとで消す！
        exit;
    } catch (Exception $e) {
        echo "エラー：" . $e->getMessage(); //デバッグ用。あとで消す！
        exit;
    } finally {
        $db->close();
    }
