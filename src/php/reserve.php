<?php
    session_start();
    require_once '../db_connect.php';

    if (!isset($_POST['book_id'])) {
        $_SESSION['message'] = "不正な操作です";
        header("Location: test.php");
        exit();
    }
    $book_id = $_POST['book_id'];
    $school_id = '';
    $grade = '';
    $class = '';
    $number = '';
    $student_id = '';

    // CSRFトークン発行関数(発行するだけで、セッション変数への保存は行わないから注意！)
    function csrf_token_generate(): string {
        $toke_byte = random_bytes(16);
        $csrf_token = bin2hex($toke_byte);
        return $csrf_token;
    }
    // CSRFトークンの生成
    $csrf_token = csrf_token_generate();

    // CSRFトークンセット関数
    function set_csrf_token(String $csrf_token): void {
        // CSRF対策用のトークンをセッションに保存
        $_SESSION['csrf_token'] = $csrf_token;
        //ここでトークンを隠し属性として送るためのhtmlコードを記述
        echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') . '">';
    }
    
    // ログインチェック
    if (!isset($_SESSION['student_id']) && !isset($_SESSION['librarian_id'])) {
        // 学生または司書としてログインしていない場合、ログインページへリダイレクト
        $_SESSION['error'] = "セッションが切れました。ログインしてください。";
        
        header("Location: student_login.php");  // 暫定的に学生用ログインページへ遷移
        exit();

    } else if (isset($_SESSION['student_id'])) {
        // 学生としてログインしていた場合は、その学生のstudent_idを入れる
        $student_id = $_SESSION['student_id'];
    }

    // CSRFトークンチェック(合致しなかった場合、logout.phpにCSRFトークンを送る方法を思いつかなかったため、ここでログアウト処理)
    if(!isset($_POST['csrf_token']) || $_POST['csrf_token'] != $_SESSION['csrf_token']){

        $redirect_path = '../html/student_login.php';
        $error_param = "?error=nomal_alert";

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
    }

    try {
        $db = new db_connect();
        $db->connect();

        // トランザクション開始
        $db->pdo->beginTransaction();

        // 司書としてログインしていた場合、学生IDをここで取得
        if (empty($student_id)) {

            $school_id = $_SESSION['librarian_school_id'];
            $grade = $_SESSION['reservation_grade'];
            $class = $_SESSION['reservation_class'];
            $number = $_SESSION['reservation_number'];

            $sql = "SELECT student_id FROM student WHERE school_id = :school_id AND grade = :grade AND class = :class AND number = :number";
            $stmt = $db->pdo->prepare($sql);
            $stmt->bindValue(':school_id', $school_id, PDO::PARAM_INT);
            $stmt->bindValue(':grade', $grade, PDO::PARAM_INT);
            $stmt->bindValue(':class', $class, PDO::PARAM_INT);
            $stmt->bindValue(':number', $number, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $student_id = $row['student_id'];

            } else {
                $_SESSION['message'] = "指定された学生情報が見つかりません";    // 一応、司書としてはログインしている
                header("Location: ../html/reservation_confirm.php");
                exit();
            }
        }

        // 書籍所蔵テーブルで書籍状態（とその他もろもろ）を取得しつつ、テーブルのレコードをロック（悲観的ロック）
        $sql = "SELECT * FROM book_stack WHERE book_id = :book_id FOR UPDATE";
        $stmt = $db->pdo->prepare($sql);
        $stmt->bindValue(':book_id', $book_id, PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);


        // 指定された本が存在した場合（reservation_confirmでも確認はしているが、一応ここでもやっておく）
        if ($row) {
            $book_status = intval($row['status_id']);
            $deny_status = [11, 12, 13];   //予約できないブラックリスト（今回は、とりあえず本が物理的に普通なら、一つの本に続けて予約を許すようにする）

            if (in_array($book_status, $deny_status)) {
                $db->pdo->rollback();
                $_SESSION['message'] = "この本は現在、貸出することができません";
                header("Location: ../html/test.php");   // ここは多分ファイル名変わる
                exit();
            }
            
            // 既に予約済みの予約をしないようにチェック
            $sql = "SELECT COUNT(*) FROM reservation WHERE student_id = :student_id AND book_id = :book_id AND status_id = :status_id";    
            $stmt = $db->pdo->prepare($sql);
            $stmt->bindValue(':student_id', $student_id, PDO::PARAM_INT);
            $stmt->bindValue(':book_id', $book_id, PDO::PARAM_STR);
            $stmt->bindValue(':status_id', 1, PDO::PARAM_INT);
            $stmt->execute();
            $count = $stmt->fetchColumn();

            if ($count > 0) {
                $_SESSION['message'] = "あなたはこの本をすでに予約しています";
                header("Location: ../html/test.php");   // ここは多分ファイル名変わる
                exit();
            }

            // 乱数を生成
            $formatted_code = '';
            do {
                $rv_code = random_int(0,9999);
                $formatted_code = sprintf('%04d', $rv_code);
                
                $sql = "SELECT COUNT(*) FROM reservation WHERE reservation_number = :reservation_number AND status_id = :status_id";    
                $stmt = $db->pdo->prepare($sql);
                $stmt->bindValue(':reservation_number', $formatted_code, PDO::PARAM_STR);
                $stmt->bindValue(':status_id', 1, PDO::PARAM_INT);
                $stmt->execute();
                $count = $stmt->fetchColumn();

            } while($count > 0);

            // 予約レコードを生成
            $sql = "INSERT INTO reservation (reservation_number, student_id, book_id, status_id) VALUES (:reservation_number, :student_id, :book_id, :status_id)";
            $stmt = $db->pdo->prepare($sql);
            $stmt->bindValue(':reservation_number', $formatted_code, PDO::PARAM_STR);
            $stmt->bindValue(':student_id', $student_id, PDO::PARAM_INT);
            $stmt->bindValue(':book_id', $book_id, PDO::PARAM_STR);
            $stmt->bindValue(':status_id', 1, PDO::PARAM_INT);
            $stmt->execute();

            // 貸出可能となっている場合のみ、書籍状態を3（予約済み）にする
            if ($book_status == 1) {
                $sql = "UPDATE book_stack SET status_id = :status_id WHERE book_id = :book_id";
                $stmt = $db->pdo->prepare($sql);
                $stmt->bindValue(':status_id', 3, PDO::PARAM_INT);
                $stmt->bindValue(':book_id', $book_id, PDO::PARAM_STR);
                $stmt->execute();
            }

            $db->pdo->commit();

            $_SESSION['message'] = "予約処理が正常に実施されました";
            header("Location: ../html/test.php");   // ここは多分ファイル名変わる
            exit();


        // 指定された本が存在しなかった場合
        } else {
            $db->pdo->rollback();
            $_SESSION['message'] = "予約対象の本は存在しません";
            header("Location: ../html/test.php");
            exit();
        }

    } catch (PDOException $e) {
        $db->pdo->rollback();
        echo "データベースエラー：" . $e->getMessage(); //デバッグ用。あとで消す！
        exit;
    } catch (Exception $e) {
        $db->pdo->rollback();
        echo "エラー：" . $e->getMessage(); //デバッグ用。あとで消す！
        exit;
    } finally {
        $db->close();
    }
?>