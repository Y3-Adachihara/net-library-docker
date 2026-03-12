<?php
    session_start();
    require_once '../db_connect.php';

    if (!isset($_SESSION['librarian_id'])) {
        header("Location: librarian_login.php");
        exit();
    }

    $librarian_school_id = $_SESSION['librarian_school_id'];
    $search_results = [];
    $search_keyword = '';

    // HTMLエスケープ関数
    function h($str) {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }

    $error_message = null;
    // 検索ボタンが押された場合
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['keyword'])) {
        $search_keyword = trim($_GET['keyword']);
        if ($search_keyword !== '') {
            try {
                $db = new db_connect();
                $db->connect();

                // 自校の本のみをタイトル検索
                $sql = "SELECT bs.book_id, bi.title, bi.author_name, bs.status_id";
                $sql .= " FROM book_stack AS bs";
                $sql .= " INNER JOIN book_info AS bi ON bs.isbn = bi.isbn";
                $sql .= " WHERE bs.school_id = :school_id";
                $sql .= " AND bi.title LIKE :keyword";
                $sql .= " ORDER BY bi.title ASC LIMIT 20";

                $stmt = $db->pdo->prepare($sql);
                $stmt->bindValue(':school_id', $librarian_school_id, PDO::PARAM_INT);
                $stmt->bindValue(':keyword', '%' . $search_keyword . '%', PDO::PARAM_STR);
                $stmt->execute();
                $search_results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            } catch (PDOException $e) {
                error_log("DBエラー：" . $e->getMessage());
                $error_message = "データベース通信エラーが発生しました。しばらく経ってからやり直してください。";
            } catch (PDOexception $e) {
                error_log("予期しないエラー: " . $e->getMessage());
                $error_message = "予期せぬエラーが発生しました。システム管理者にお問い合わせください。";
            } finally {
                $db->close();
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>貸出禁止設定 - 入力</title>
    <link rel="stylesheet" href="../css/librarian_myPage.css">
    <style>
        .input-section { margin-bottom: 30px; padding: 15px; border: 1px solid #ccc; background: #f9f9f9; }
        .search-result-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .search-result-table th, .search-result-table td { border: 1px solid #ddd; padding: 8px; }
        .date-input { width: 140px; }
    </style>
</head>
<body>
    <?php
        // セッションにメッセージが保存されていたらアラートを表示して消去する
        if (isset($_SESSION['message'])) {
            $msg = $_SESSION['message'];
            // JSのコードとして出力（h関数でエスケープ）
            echo "<script>alert('" . h($msg) . "');</script>";
            // 一度表示したら消す
            unset($_SESSION['message']);
        }
    ?>

    <?php if ($error_message !== null): ?>
        <div style="color: red; padding: 20px; border: 1px solid red; background-color: #fee; text-align: center;">
                <h3>現在システムをご利用いただけません</h3>
                <p><?php echo h($error_message); ?></p>
            </div>
        <?php else: ?>
    <header class="main-header">
        <div class="header-logo">貸出禁止設定</div>
        <nav class="header-nav">
            <ul><li><a href="../html/librarian_myPage.php">戻る</a></li></ul>
        </nav>
    </header>

    <div class="info-table-container">
        
        <div class="input-section">
            <h3>方法1: 書籍IDを直接入力</h3>
            <form action="librarian_lending_deny_confirm.php" method="POST">
                <label>書籍ID: <input type="number" name="book_id" required placeholder="例: 10001"></label><br><br>
                <label>開始日時: <input type="datetime-local" name="start_date" required value="<?php echo date('Y-m-d\TH:i'); ?>"></label><br>
                <label>終了日時: <input type="datetime-local" name="end_date" required></label><br><br>
                <button type="submit">確認画面へ</button>
            </form>
        </div>

        <div class="input-section">
            <h3>方法2: タイトルから検索して指定</h3>
            <form action="" method="GET">
                <input type="text" name="keyword" value="<?php echo h($search_keyword); ?>" placeholder="タイトルの一部を入力">
                <button type="submit">検索</button>
            </form>

            <?php if (!empty($search_results)): ?>
                <table class="search-result-table">
                    <thead>
                        <tr>
                            <th>書籍ID</th>
                            <th>タイトル</th>
                            <th>著者</th>
                            <th>禁止期間設定</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($search_results as $row): ?>
                        <tr>
                            <td><?php echo h($row['book_id']); ?></td>
                            <td><?php echo h($row['title']); ?></td>
                            <td><?php echo h($row['author_name']); ?></td>
                            <td>
                                <form action="librarian_lending_deny_confirm.php" method="POST">
                                    <input type="hidden" name="book_id" value="<?php echo h($row['book_id']); ?>">
                                    Start: <input type="datetime-local" name="start_date" class="date-input" required value="<?php echo date('Y-m-d\TH:i'); ?>"><br>
                                    End: &nbsp;<input type="datetime-local" name="end_date" class="date-input" required><br>
                                    <button type="submit" style="margin-top:5px;">この本を設定</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php elseif ($search_keyword !== ''): ?>
                <p>該当する自校の書籍は見つかりませんでした。</p>
            <?php endif; ?>
        </div>
    </div>
</body>
<?php endif; ?>
</html>