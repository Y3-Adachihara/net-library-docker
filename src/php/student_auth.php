<?php 
    require_once '../db_connect.php';
    session_start();

    $school_id = $_POST["school"];
    $grade = $_POST["grade"];
    $class = $_POST["class"];
    $number = $_POST["number"];
    $password = $_POST['password'];

    //CSRF対策
    //トークンが一致しない時と、そもそもトークンがlogin.php(ログイン画面)から送られていない時
    if (!isset($_POST["csrf_token"]) || $_POST["csrf_token"] != $_SESSION["csrf_token"]) {
        $_SESSION['error'] = "CSFS対策に引っかかりました（開発者向けエラーメッセージ）";
        header("Location: ../html/student_login.php");
        exit();
    }

    try {
        $db = new db_connect();
        $db->connect(); //データベースへ接続

        $stmt_select =$db->pdo->prepare("SELECT table_id, password FROM student WHERE school_id = :school_id AND grade =:grade AND class =:class AND number = :number");
        
        $stmt_select->bindValue(':school_id', $school_id, PDO::PARAM_INT);
        $stmt_select->bindValue(':grade', $grade, PDO::PARAM_INT);
        $stmt_select->bindValue(':class', $class, PDO::PARAM_STR);
        $stmt_select->bindValue(':number', $number, PDO::PARAM_INT);
        
        $stmt_select->execute();
        $row= $stmt_select->fetch(PDO::FETCH_ASSOC);


        //パスワードが取得できており、かつPOSTでパスワードが送られてきている場合
        if ($row != false && isset($_POST['password'])) {
            $passwd_hash = $row['password'];
            
            //パスワードを比較
            if(password_verify($_POST['password'], $passwd_hash)) {

                //セッション固定化攻撃対策
                session_regenerate_id(true);

                //ここでのtable_idは先のSELECT文で取得した利用者ID
                $_SESSION['student_id'] = $row['table_id'];
                //まだ学生マイページが用意できていないため、とりあえず司書マイページへリダイレクト
                header("Location: ../html/myPage_librarian.php");
                exit();

            } else {
	            $_SESSION['error'] = "IDまたはパスワードが違います。";
                header("Location: ../html/student_login.php");
                exit();
            }
        } else {
            $_SESSION['error'] = "パスワードの取得に失敗しました。";
            header("Location: ../html/student_login.php");
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

?>