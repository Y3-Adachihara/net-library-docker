<?php
    session_start();
    require_once '../db_connect.php';

    $book_id = $_POST['book_id'];
    $school_id = $_SESSION['librarian_school_id'];
    $grade = $_SESSION['reservation_grade'];
    $class = $_SESSION['reservation_class'];
    $number = $_SESSION['reservation_number'];
    $student_id = '';
    
    // ログインチェック
    if (!isset($_SESSION['student_id']) && !isset($_SESSION['librarian_id'])) {
        // 学生または司書としてログインしていない場合、ログインページへリダイレクト
        $_SESSION['error'] = "ログインしてください。";
        header("Location: student_login.php");
        exit();

    } else if (isset($_SESSION['student_id'])) {
        // 学生としてログインしていた場合は、その学生のstudent_idを入れる
        $student_id = $_SESSION['student_id'];
    }

    // CSRFトークンチェック
    if(!isset($_POST['csrf_token']) || $_POST['csrf_token'] != $_SESSION['csrf_token']){
        $_SESSION['message'] = "不正な予約リクエストです";
        header("Location: ../html/student_login.php");
        exit();
    }

    try {
        $db->pdo->beginTransaction();

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
            $_SESSION['message'] = "司書としてログインしてください";
            header("Location: ../html/librarian_login.php");
            exit();
        }



        // 書籍所蔵テーブルで書籍状態（とその他もろもろ）を取得しつつ、テーブルのレコードをロック（悲観的ロック）
        $sql = "SELECT * FROM book_stack WHERE book_id = :book_id FOR UPDATE";
        $stmt = $db->pdo->prepare($sql);
        $stmt->bindValue(':book_id', $book_id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);


        // 指定された本が存在した場合（reservation_confirmでも確認はしているが、一応ここでもやっておく）
        if ($row) {
            $book_status = intval($row['book_status']);
            $allow_status = [11, 12, 13];   //予約できないブラックリスト（今回は、とりあえず本が物理的に普通なら多重予約を許すようにする）

            if (in_array($book_status, $allow_status)) {
                $db->pdo->rollback();
                $_SESSION['message'] = "この本は現在、貸出することができません";
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
                $count = $stmt->fetchColumn(FETCH_ASSOC);

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
            header("Location: ../html/test.php")
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