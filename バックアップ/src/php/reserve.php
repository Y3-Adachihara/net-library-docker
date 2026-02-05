<?php
    session_start();
    require_once '../db_connect.php';

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

    if (!isset($_POST['book_id'])) {
        $_SESSION['message'] = "不正な操作です";
        header("Location: test.php");
        exit();
    }
    $book_id = $_POST['book_id'];
    $student_id = '';
    $student_school_id = '';    // 生徒としてログインしていた場合の学生ID
    $librarian_school_id = '';  // 司書としてログインしていた場合の学生ID
    $grade = '';
    $class = '';
    $number = '';

    $deny_start = null;
    $deny_end = null;
    
    
    // ログインチェック
    if (!isset($_SESSION['student_id']) && !isset($_SESSION['librarian_id'])) {
        // 学生または司書としてログインしていない場合、ログインページへリダイレクト
        $_SESSION['error'] = "セッションが切れました。ログインしてください。";
        
        header("Location: student_login.php");  // 暫定的に学生用ログインページへ遷移
        exit();

    } else if (isset($_SESSION['student_id'])) {
        // 学生としてログインしていた場合は、その学生のstudent_idを入れる
        $student_id = $_SESSION['student_id'];
        $student_school_id = $_SESSION['student_school_id'];
    }

    try {
        $db = new db_connect();
        $db->connect();

        // トランザクション開始
        $db->pdo->beginTransaction();

        // 司書としてログインしていた場合
        if (empty($student_id)) {

            $librarian_school_id = $_SESSION['librarian_school_id'];
            $grade = $_SESSION['reservation_grade'];
            $class = $_SESSION['reservation_class'];
            $number = $_SESSION['reservation_number'];

            // 学生IDを取得
            $sql = "SELECT student_id FROM student WHERE school_id = :school_id AND grade = :grade AND class = :class AND number = :number";
            $stmt = $db->pdo->prepare($sql);
            $stmt->bindValue(':school_id', $librarian_school_id, PDO::PARAM_INT);
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

        // 書籍所蔵テーブルから書籍状態（とその他もろもろ）を取得しつつ、テーブルのレコードをロック（悲観的ロック）
        $sql = "SELECT * FROM book_stack WHERE book_id = :book_id FOR UPDATE";
        $stmt = $db->pdo->prepare($sql);
        $stmt->bindValue(':book_id', $book_id, PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // 貸出禁止期間に入っていた場合、禁止期間終了日と、一応開始日を取得しておく（表示用。予約はできるけど、メッセージは出すため。）
        $sql = "SELECT start_date, end_date FROM lending_deny AS ld 
                WHERE ld.book_id = :book_id  
                AND (
                    DATE(NOW()) >= DATE_SUB(DATE(ld.start_date), INTERVAL 3 DAY) 
                    AND 
                    DATE(NOW()) <= DATE(ld.end_date)
                )";
        $stmt = $db->pdo->prepare($sql);
        $stmt->bindValue(':book_id', $book_id, PDO::PARAM_STR);
        $stmt->execute();
        $is_denied = $stmt->fetch(PDO::FETCH_ASSOC);

        // ↑で取得できていた場合、禁止期間の開始日と終了日を格納させる
        if ($is_denied) {
            $deny_start = date('Y年m月d日', strtotime($is_denied['start_date']));
            $deny_end = date('Y年m月d日', strtotime($is_denied['end_date']));
        }
        
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

            // 予約の認証で使う乱数を生成
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

            $_SESSION['message'] = "予約処理が正常に実施されました。";
            if ($is_denied) {
                $available_date = date('Y年m月d日', strtotime($is_denied['start_date'] . '+1 day'));

                // 予約した本が貸出禁止期間に入っていた場合は、こいつらを付け足す
                $_SESSION['message'] .= "\nこの本は" . $deny_start . " から " . $deny_end . "まで、貸出禁止期間となっています。";
                $_SESSION['message'] .= "\nそのため、貸出は" . $available_date . "以降となります。ご了承ください。";
            }
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