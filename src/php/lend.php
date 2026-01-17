<?php
    require_once '../db_connect.php';
    session_start();

    $grade = $_POST['school-year'];
    $class = $_POST['calss'];
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

    $librarian_id = $_SESSION['librarian_id'];

    function lending($grade, $class, $number, $book_id) {
        //現在の日付を取得
        $current_date = new DateTimeImmutable();
        $current_date_str = $current_date->format('Y-m-d H:i:s');

        $lending_sql = "INSERT INTO lending student_id, book_id, lending_date)";
    }

    try {
        $db = new db_connect();
        $db->connect(); //データベースへ接続

        // 入力された学生情報を取得
        $student_select = "SELECT * FROM student WHERE grade = :grade AND class = :class AND number = :number";
        $stmt = $db->pdo->prepare($student_select);
        $stmt->bindValue(':grade', $grade, PDO::PARAM_INT);
        $stmt->bindValue(':class', $class, PDO::PARAM_INT);
        $stmt->bindValue(':number', $number, PDO::PARAM_INT);
        $stmt->execute();
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        // 検索条件に指定された書籍が存在するか確認
        $book_isExists = "SELECT book_status FROM book WHERE book_id = :book_id";
        $stmt = $db->pdo->prepare($book_isExists);
        $stmt->bindValue(':book_id', $book_id, PDO::PARAM_STR);
        $stmt->execute();
        $status = $stmt->fetch(PDO::FETCH_ASSOC);

        //入力された学生と書籍が存在した場合
        if ($student || $status) {
            $old_bookStatus = intval($status['book_status'],10);
            $student_id = intval($student['student_id'],10);
            
            switch ($book_status) {
                case 1: //貸出可能

                    //トランザクション開始
                    $db->pdo->beginTransaction();

                    // もし、この段階でも書籍状態が1(貸出可能)であれば、書籍状態を変更
                    $update_bookStatus = "UPDATE book SET book_status = :neo_bookStatus WHERE book_id = :book_id AND book_status = :old_bookStatus";
                    $stmt = $db->pdo->prepare($update_bookStatus);
                    $stmt->bindValue(':neo_bookStatus', 2, PDO::FETCH_ASSOC);
                    $stmt->bindValue(':book_id', $book_id, PDO::FETCH_ASSOC);
                    $stmt->bindValue(':book_status', $old_bookStatus, PDO::FETCH_ASSOC);
                    $stmt->execute();

                    //現在の日付を取得（lendingテーブルの構成変更後は消す！）
                    $current_date = new DateTimeImmutable();
                    $current_date_str = $current_date->format('Y-m-d H:i:s');

                    $inset_LendRec = "INSERT INTO lending(studen_id, book_id, lending_date) VALUES(:student_id, :book_id, :lending_date)";
                    $stmt = $db->pdo->prepare($inset_LendRec);
                    $stmt->bindValue(':student_id', $student_id, PDO::FETCH_ASSOC);
                    $stmt->bindValue(':book_id', $book_id, PDO::FETCH_ASSOC);
                    $stmt->bindValue(':lending_date', $, PDO::FETCH_ASSOC);

                    


            }



            if ($status_array) {
                $book_status = intval($status_array['book_status'], 10);
                switch($book_status) {
                    case 1:

                    case 2;
                }

            }
        }

    } catch (PDOException $e) {
        echo "データベースエラー：" . $e->getMessage(); //デバッグ用。あとで消す！
        exit;
    } catch (Exception $e) {
        echo "エラー：" . $e->getMessage(); //デバッグ用。あとで消す！
        exit;
    }


?>