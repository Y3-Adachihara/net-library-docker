<?php
    require_once '../db_connect.php';
    session_start();

    $grade = $_SESSION['grade'] ?? '';
    $class = $_SESSION['class'] ?? '';
    $number = $_SESSION['number'] ?? '';
    $book_id = $_SESSION['book_id'] ?? '';

    // 空の状態はfalseとみなされる。！は否定演算子なので、どれか空だったらtrueで引っ掛かる
    if (!$grade || !$class || !$number || !$book_id) {
        $_SESSION['lend_result_message'] = "セッション有効切れ、または不正なアクセスです";
        header("Location: ../html/貸出返却.php");
        exit();
    }

    $student_id = '';

    // 司書としてログインしていなければ、ログイン画面へリダイレクト
    if (!isset($_SESSION['librarian_id'])) {
        header("Location: librarian_login.php");
        exit();
    }

    //CSRF対策
    //トークンが一致しないか（右）、そもそもトークンがlogin.php(ログイン画面)から送られていない（左）時
    if (!isset($_POST["csrf_token"]) || $_POST["csrf_token"] != $_SESSION["csrf_token"]) {
        $_SESSION['error'] = "不正な貸出リクエストです";
        header("Location: ../html/貸出返却.php");
        exit(); 
    }

    $librarian_school_id = intval($_SESSION['librarian_school_id']);
    
    // 入力された予約番号
    $user_input_resNumber = $_POST['reservation_number'];

    try {
        $db = new db_connect();
        $db->connect();

        $db->pdo->beginTransaction();

        // reserved_book_lend.php（予約番号確認して貸出or戻す）に渡すためのやつら
        $sql = "SELECT student_id FROM student WHERE school_id = :school_id AND grade = :grade AND class = :class AND number = :number";
        $stmt = $db->pdo->prepare($sql);
        $stmt->bindValue(':school_id', $librarian_school_id, PDO::PARAM_INT);
        $stmt->bindValue(':grade', $grade, PDO::PARAM_INT);
        $stmt->bindValue(':class', $class, PDO::PARAM_STR);
        $stmt->bindValue(':number', $number, PDO::PARAM_INT);
        $stmt->execute();
        $is_stu_exists = $stmt->fetch(PDO::FETCH_ASSOC);

        // 学生が存在した場合
        if ($is_stu_exists) {
            // 学生テーブルの主キーを格納
            $student_id = $is_stu_exists['student_id'];
              
            // 予約番号を取得
            $sql = "SELECT r.reservation_id, r.reservation_number, bs.book_id AS bs_book_id, bs.status_id AS bs_status, r.status_id AS res_status, r.student_id";
            $sql .= " FROM reservation AS r LEFT OUTER JOIN book_stack AS bs ON r.book_id = bs.book_id";
            $sql .= " WHERE r.student_id = :student_id AND r.book_id = :book_id AND bs.status_id IN (:status_id1, :status_id2) FOR UPDATE";
            $get_resNum_stmt = $db->pdo->prepare($sql);
            $get_resNum_stmt->bindValue(':student_id', $student_id, PDO::PARAM_INT);
            $get_resNum_stmt->bindValue(':book_id', $book_id, PDO::PARAM_STR);
            $get_resNum_stmt->bindValue(':status_id1', 4, PDO::PARAM_INT);   //　予約受取俟ち
            $get_resNum_stmt->bindValue(':status_id2', 7, PDO::PARAM_INT);   //　配送予約受取待ち
            $get_resNum_stmt->execute();
            $resConfirm_row = $get_resNum_stmt->fetch(PDO::FETCH_ASSOC);


            // 値が取得できており、かつ入力された予約番号が予約と一緒だった時
            if ($resConfirm_row && intval($resConfirm_row['reservation_number'], 10) == $user_input_resNumber) {

                // 予約状態を更新
                $sql = "UPDATE reservation SET status_id = :completed_res_status WHERE reservation_id = :reservation_id AND status_id = :res_old_status";
                $res_stmt = $db->pdo->prepare($sql);
                $res_stmt->bindValue(':completed_res_status', 3, PDO::PARAM_INT);   // 予約状態を予約完了に
                $res_stmt->bindValue(':reservation_id', $resConfirm_row['reservation_id'], PDO::PARAM_STR);
                $res_stmt->bindValue(':res_old_status', 1, PDO::PARAM_INT); // 予約状態が1:予約完了（予約を受け取ってはいない）
                $res_stmt->execute();

                // 書籍状態を更新
                $sql = "UPDATE book_stack SET status_id  = :received_bk_status WHERE book_id = :reserved_book_id AND status_id IN (:old_status1, :old_status2)";
                $bk_stmt = $db->pdo->prepare($sql);
                $bk_stmt->bindValue(':received_bk_status', 2, PDO::PARAM_INT); // 借りられた本の状態を貸出中に
                $bk_stmt->bindValue(':reserved_book_id', $book_id, PDO::PARAM_STR);
                $bk_stmt->bindValue(':old_status1', 4, PDO::PARAM_INT);  // 自校内から予約した本の貸出
                $bk_stmt->bindValue(':old_status2', 7, PDO::PARAM_INT); // 他校から予約した本の貸出                
                $bk_stmt->execute();

                // 予約テーブルと書籍所蔵テーブルの両方で更新が正常に完了したら
                if ($res_stmt->rowCount() == 1 && $bk_stmt->rowCount()) {

                    // 貸出レコードを生成
                    $inset_LendRec = "INSERT INTO lending(student_id, book_id) VALUES(:student_id, :book_id)";
                    $insert_lenRec = $db->pdo->prepare($inset_LendRec);
                    $insert_lenRec->bindValue(':student_id', $student_id, PDO::PARAM_INT);
                    $insert_lenRec->bindValue(':book_id', $book_id, PDO::PARAM_STR);
                    $insert_lenRec->execute();

                    $db->pdo->commit();
                    $_SESSION['lend_result_message'] = "貸し出し処理が完了しました。";
                    header("Location: ../html/貸出返却.php");
                    exit();
                    
                } else if ($res_stmt->rowCount() != 1) {
                    $db->pdo->rollback();
                    $message = "予約更新処理が失敗しました。";
                } else if ($bk_stmt->rowCount() != 1) {
                    $db->pdo->rollback();
                    $message = "書籍状態更新処理が失敗しました。";
                } else {
                    $db->pdo->rollback();
                    $message = "貸出処理が失敗しました。";
                }                

            } else {
                $db->pdo->rollback();
                $message = "指定された番号の予約が存在しません。";
            }
            
        } else {
            $db->pdo->rollback();
            $message = "セッション有効切れ、または存在しない学生です";
        }

        $_SESSION['reserved_book_lend_message'] = $message;
        header("Location: ../html/verify_resCode.php");
        exit();

    
    } catch (PDOException $e) {
        $db->pdo->rollback();
        echo "データベースエラー：" . $e->getMessage(); //デバッグ用。あとで消す！
        exit;
    } catch (Exception $e) {
        $db->pdo->rollback();
        echo "エラー：" . $e->getMessage(); //デバッグ用。あとで消す！
        exit;
    }
?>