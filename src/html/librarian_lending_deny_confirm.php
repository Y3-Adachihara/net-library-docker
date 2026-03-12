<?php
    session_start();
    require_once '../db_connect.php';

    if (!isset($_SESSION['librarian_id'])) {
        header("Location: librarian_login.php");
        exit();
    }

    $librarian_school_id = $_SESSION['librarian_school_id'];
    $book_id = $_POST['book_id'] ?? null;
    $start_date = $_POST['start_date'] ?? null;
    $end_date = $_POST['end_date'] ?? null;

    if (!$book_id || !$start_date || !$end_date) {
        $_SESSION['message'] = "入力情報が不足しています。";
        header("Location: librarian_lending_deny_input.php");
        exit();
    }

    // HTMLエスケープ
    function h($str) {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }

    // CSRFトークン生成
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
    }
    $csrf_token = $_SESSION['csrf_token'];

    $book_info = null;

    $error_message = null;

    try {
        $db = new db_connect();
        $db->connect();

        // 書籍情報の取得と自校チェック
        $sql = "SELECT bs.book_id, bi.title, bi.author_name, bs.school_id, sc.school_name";
        $sql .= " FROM book_stack AS bs";
        $sql .= " INNER JOIN book_info AS bi ON bs.isbn = bi.isbn";
        $sql .= " LEFT JOIN school AS sc ON bs.school_id = sc.school_id";
        $sql .= " WHERE bs.book_id = :book_id";
        
        $stmt = $db->pdo->prepare($sql);
        $stmt->bindValue(':book_id', $book_id, PDO::PARAM_INT);
        $stmt->execute();
        $book_info = $stmt->fetch(PDO::FETCH_ASSOC);

        // チェック処理
        if (!$book_info) {
            $error_msg = "指定された書籍IDが存在しません。";
        } elseif ($book_info['school_id'] != $librarian_school_id) {
            $error_msg = "自校（" . h($_SESSION['school_name']) . "）以外の書籍には設定できません。<br>この本の所蔵： " . h($book_info['school_name']);
        } elseif ($start_date >= $end_date) {
            $error_msg = "終了日時は開始日時より後に設定してください。";
        }

    } catch (PDOException $e) {
        error_log("DBエラー：" . $e->getMessage());
        $error_message = "データベース通信エラーが発生しました。しばらく経ってからやり直してください。";
    } catch (PDOexception $e) {
        error_log("予期しないエラー: " . $e->getMessage());
        $error_message = "予期せぬエラーが発生しました。システム管理者にお問い合わせください。";
    } finally {
        $db->close();
    }
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>貸出禁止設定 - 確認</title>
    <link rel="stylesheet" href="../css/librarian_myPage.css">
</head>
<body>
    <?php if ($error_message !== null): ?>
        <div style="color: red; padding: 20px; border: 1px solid red; background-color: #fee; text-align: center;">
                <h3>現在システムをご利用いただけません</h3>
                <p><?php echo h($error_message); ?></p>
            </div>
    <?php else: ?>
    <div class="info-table-container">
        <h2>以下の内容で貸出禁止（拒否）を設定しますか？</h2>

        <?php if (isset($error_msg)): ?>
            <p style="color: red; font-weight: bold;"><?php echo $error_msg; ?></p>
            <button onclick="history.back()">戻る</button>
        <?php else: ?>
            <table class="info-table">
                <tr><th>書籍ID</th><td><?php echo h($book_info['book_id']); ?></td></tr>
                <tr><th>タイトル</th><td><?php echo h($book_info['title']); ?></td></tr>
                <tr><th>著者</th><td><?php echo h($book_info['author_name']); ?></td></tr>
                <tr><th>禁止開始</th><td><?php echo h(str_replace('T', ' ', $start_date)); ?></td></tr>
                <tr><th>禁止終了</th><td><?php echo h(str_replace('T', ' ', $end_date)); ?></td></tr>
            </table>

            <form action="../php/lending_deny_insert.php" method="POST" style="margin-top: 20px;">
                <input type="hidden" name="csrf_token" value="<?php echo h($csrf_token); ?>">
                <input type="hidden" name="book_id" value="<?php echo h($book_id); ?>">
                <input type="hidden" name="start_date" value="<?php echo h($start_date); ?>">
                <input type="hidden" name="end_date" value="<?php echo h($end_date); ?>">
                
                <button type="submit">確定する</button>
                <button type="button" onclick="history.back()" style="background-color: #999;">戻る</button>
            </form>
        <?php endif; ?>
    </div>
</body>
<?php endif; ?>
</html>