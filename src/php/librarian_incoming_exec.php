<?php
session_start();
require_once '../db_connect.php';

// CSRFチェックやログインチェックは省略

$update_items = $_POST['update_items'] ?? [];

if(empty($update_items)) {
    $_SESSION['message'] = "更新する書籍リストを取得できませんでした";
    header("Location: ../html/librarian_incoming_books.php");
    exit();
}

    try {
        $db = new db_connect();
        $db->connect();
        $pdo = $db->pdo;

        $pdo->beginTransaction();

        $sql = "UPDATE book_stack SET status_id = :next_status WHERE book_id = :book_id";
        $stmt = $pdo->prepare($sql);

        foreach ($update_items as $item) {
            $book_id = $item['book_id'];
            $next_status = $item['next_status'];

            $stmt->bindValue(':next_status', $next_status, PDO::PARAM_INT);
            $stmt->bindValue(':book_id', $book_id, PDO::PARAM_STR);
            $stmt->execute();
        }

        $pdo->commit();
        $_SESSION['message'] = "書籍の状態を更新しました。";

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['message'] = "エラーが発生しました: " . $e->getMessage();
    }


header("Location: ../html/librarian_incoming_books.php");
exit();
?>