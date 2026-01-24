<?php
    require_once '../db_connect.php';
    session_start();

    $grade = $_POST['school-year'];
    $class = $_POST['class'];
    $number = $_POST['number'];
    $book_id = $_POST['id-number'];

    //CSRF対策
    //トークンが一致しないか（右）、そもそもトークンがlogin.php(ログイン画面)から送られていない（左）時
    if (!isset($_POST["csrf_token"]) || $_POST["csrf_token"] != $_SESSION["csrf_token"]) {
        $_SESSION['error'] = "CSFS対策に引っかかりました（開発者向けエラーメッセージ）";    //本番はメッセージの内容を変える
        header("Location: ../html/librarian_login.php");
        exit(); 
    }

    // 司書としてログインしていなければ、ログイン画面へリダイレクト
    if (!isset($_SESSION['librarian_id'])) {
        header("Location: librarian_login.php");
        exit();
    }

    $librarian_school_id = intval($_SESSION['librarian_school_id']);

    try {
        $db = new db_connect();
        $db->connect(); //データベースへ接続
        $message = "";

        //トランザクション開始
        $db->pdo->beginTransaction();

        $sql = "SELECT l.book_id, b_s.school_id FROM lending AS l LEFT OUTER JOIN student AS s";
        $sql .= " ON L.student_id = s.student_id";
        $sql .= " LEFT OUTER JOIN book_stack AS b_s";
        $sql .= " ON l.book_id = b_s.book_id WHERE";
        $sql .= " s.school_id = :school_id AND";
        $sql .= " s.grade = :grade AND";
        $sql .= " s.class = :class AND";
        $sql .= " s.number = :number AND";
        $sql .= " l.book_id = :book_id AND";
        $sql .= " l.return_date IS NULL";
        $sql .= " FOR UPDATE OF l";  // 悲観的ロックをlendingテーブルにのみ適用
        $stmt = $db->pdo->prepare($sql);

        $stmt->bindValue(':school_id', $librarian_school_id, PDO::PARAM_INT);
        $stmt->bindValue(':grade', $grade, PDO::PARAM_INT);
        $stmt->bindValue(':class', $class, PDO::PARAM_INT);
        $stmt->bindValue(':number', $number, PDO::PARAM_INT);
        $stmt->bindValue(':book_id', $book_id, PDO::PARAM_STR);
        $stmt->execute();
        $is_lended = $stmt->fetch(PDO::FETCH_ASSOC);    //二冊同時にまとめて返却はできない仕様（だったはず）

        if (!$is_lended) {
            $db->pdo->rollback();
            $_SESSION['return_result_message'] = "指定された本は貸出されていないか、存在しません。もしくは、すでに返却済みです";
            header("Location: ../html/貸出返却.php");
            exit();
        }

        $book_id = $is_lended["book_id"];
        $book_belong = intval($is_lended['school_id']);

        $sql = "SELECT * FROM reservation WHERE book_id = :book_id AND status_id = :status_id ORDER BY created_at ASC";
        $stmt = $db->pdo->prepare($sql);
        $stmt->bindValue(':book_id', $book_id, PDO::PARAM_STR);
        $stmt->bindValue(':status_id', 1, PDO::PARAM_INT);
        $stmt->execute();
        $is_reserved = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 現在時刻を取得
        $current_date = new DateTimeImmutable();
        $current_date_str = $current_date->format('Y-m-d H:i:s');


        // 自校所有の本であり、予約が入っていない場合
        if ($book_belong == $_SESSION['librarian_school_id'] && empty($is_reserved)) {
            $sql = "UPDATE book_stack SET status_id = :status_id WHERE book_id = :book_id";
            $stmt = $db->pdo->prepare($sql);
            $stmt->bindValue(':status_id', 1, PDO::PARAM_INT);
            $stmt->bindValue(':book_id', $book_id, PDO::PARAM_STR);
            $stmt->execute();

        // それ以外は、「10:検品・仕分け中」状態を挟む
        } else {
            $sql = "UPDATE book_stack SET status_id = :status_id WHERE book_id = :book_id";
            $stmt = $db->pdo->prepare($sql);
            $stmt->bindValue(':status_id', 10, PDO::PARAM_INT);
            $stmt->bindValue(':book_id', $book_id, PDO::PARAM_STR);
            $stmt->execute();
        }

        // 更新処理が成功した場合のみ、lendingテーブルの返却日に現在日時を挿入
        if ($stmt->rowCount() == 1) {
            $sql = "UPDATE lending SET return_date = :current_date WHERE book_id = :book_id AND return_date IS NULL";
            $stmt = $db->pdo->prepare($sql);
            $stmt->bindValue(':current_date', $current_date_str, PDO::PARAM_STR);
            $stmt->bindValue(':book_id', $book_id, PDO::PARAM_STR);
            $stmt->execute();
            $message = "返却処理が完了しました。";

        } else {
            $db->pdo->rollback();
            $message = "返却処理が失敗しました。";
        }

        $db->pdo->commit();
        $_SESSION['return_result_message'] = $message;
        header("Location: ../html/貸出返却.php");
        exit();

    } catch (PDOException $pe) {
        $db->pdo->rollback();
        echo "データベースエラー：" . $pe->getMessage(); //デバッグ用。あとで消す！
        exit;
    } catch (Exception $e) {
        $db->pdo->rollback();
        echo "エラー：" . $e->getMessage(); //デバッグ用。あとで消す！
        exit;
    } finally {
        $db->close();
    }
?>