<?php 
    require_once '../db_connect.php';
    session_start();

    //CSRF対策
    //トークンが一致しないか（右）、そもそもトークンがlogin.php(ログイン画面)から送られていない（左）時
    if (!isset($_POST["csrf_token"]) || $_POST["csrf_token"] != $_SESSION["csrf_token"]) {
        $_SESSION['error'] = "不正なリクエストです";
        header("Location: ../html/deliverer_login.php");
        exit(); 
    }

    $user_id = $_POST["login_id"];
    $password = $_POST['password'];

    try {
        $db = new db_connect();
        $db->connect(); //データベースへ接続

        $stmt_select = $db->pdo->prepare("SELECT * FROM deliverer WHERE login_id = :login_id");
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

                $_SESSION['deliverer_id'] = $row['deliverer_id'];
                $_SESSION['deliverer_family_name'] = $row['family_name'];
                $_SESSION['deliverer_first_name'] = $row['first_name'];
                header("Location: ../html/deliverer_deliverer_book_management.php");
                exit();

            } else {
	            $_SESSION['message'] = "IDまたはパスワードが違います。";
                header("Location: ../html/deliverer_login.php");
                exit();
            }
        } else {
            $_SESSION['message'] = "パスワードの取得に失敗しました。";
            header("Location: ../html/deliverer_login.php");
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