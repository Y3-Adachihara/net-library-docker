<?php
    session_start();
    require_once '../db_connect.php';

    if (!isset($_SESSION['librarian_id'])) {
        header("Location: ../html/librarian_login.php");
        exit();
    }

    // CSRFチェック
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("不正なリクエストです。");
    }

    $book_id = $_POST['book_id'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    
    // パスワード自動生成（数字4桁の文字列）
    // 0埋めして必ず4桁にする (例: 5 -> "0005")
    $pass_num = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);

    try {
        $db = new db_connect();
        $db->connect();

        // 念のため再度、自校の書籍かチェック（URL直接アクセス対策）
        $check_sql = "SELECT school_id FROM book_stack WHERE book_id = :book_id";
        $stmt_check = $db->pdo->prepare($check_sql);
        $stmt_check->bindValue(':book_id', $book_id, PDO::PARAM_INT);
        $stmt_check->execute();
        $book = $stmt_check->fetch(PDO::FETCH_ASSOC);

        if (!$book || $book['school_id'] != $_SESSION['librarian_school_id']) {
            $_SESSION['message'] = "権限がない、または存在しない書籍です。";
            header("Location: librarian_lending_deny_input.php");
            exit();
        }

        // 登録処理
        $sql = "INSERT INTO lending_deny (pass_num, book_id, start_date, end_date, created_at, updated_at)";
        $sql .= " VALUES (:pass_num, :book_id, :start_date, :end_date, NOW(), NOW())";
        
        $stmt = $db->pdo->prepare($sql);
        $stmt->bindValue(':pass_num', $pass_num, PDO::PARAM_STR);
        $stmt->bindValue(':book_id', $book_id, PDO::PARAM_INT);
        $stmt->bindValue(':start_date', $start_date, PDO::PARAM_STR);
        $stmt->bindValue(':end_date', $end_date, PDO::PARAM_STR);
        $stmt->execute();

        $_SESSION['message'] = "貸出禁止期間を設定しました。";
        // さっき作った一覧画面へ戻す
        header("Location: ../html/librarian_lending_deny_list.php");
        exit();

    } catch (PDOException $e) {
        echo "データベースエラー: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        exit();
    }
?>