<?php 
    require_once '../db_connect.php';
    session_start();

    $school_id = $_POST["school"];
    $grade = $_POST["grade"];
    $class = $_POST["class"];
    $number = $_POST["number"];
    $password = $_POST['password'];

    //CSRF対策
    //トークンが一致しない時と、そもそもトークンがstudent_login.php(ログイン画面)から送られていない時
    if (!isset($_POST["csrf_token"]) || $_POST["csrf_token"] != $_SESSION["csrf_token"]) {
        $_SESSION['error'] = "不正なリクエストです。";
        header("Location: ../html/student_login.php");
        exit();
    }

    try {
        $db = new db_connect();
        $db->connect(); //データベースへ接続

        $stmt_select =$db->pdo->prepare("SELECT * FROM student WHERE school_id = :school_id AND grade =:grade AND class =:class AND number = :number");
        
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
                $_SESSION['student_id'] = $row['student_id'];
                $_SESSION['student_school_id'] = $row['school_id'];
                $_SESSION['student_role_id'] = $row['role_id'];
                $_SESSION['student_grade'] = $row['grade'];
                $_SESSION['student_class'] = $row['class'];
                $_SESSION['student_number'] = $row['number'];
                $_SESSION['student_family_name'] = $row['family_name'];
                $_SESSION['student_first_name'] = $row['first_name'];
                //まだ学生マイページが用意できていないため、とりあえず司書マイページへリダイレクト
                header("Location: ../html/stu_myPage.php");
                exit();

            } else {
	            $_SESSION['message'] = "IDまたはパスワードが違います。";
                header("Location: ../html/student_login.php");
                exit();
            }
        } else {
            $_SESSION['message'] = "パスワードの取得に失敗しました。";
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