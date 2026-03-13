<?php
    session_start();
    require_once '../db_connect.php';

    header("Cache-Control:no-cache,no-store,must-revalidate,max-age=0,post-check=0,pre-check=0");
    header("Pragma:no-cache");

    if (!isset($_SESSION['student_id'])) {
        $_SESSION['message'] = "ログインしてください。";
        header("Location: student_login.php");
        exit();
    }

    $student_name = $_SESSION['student_family_name'] . " " . $_SESSION['student_first_name'];

    function h($str) {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }

    $deny_list = [];
    $error_message = null;

    try {
        $db = new db_connect();
        $db->connect();

        // ★修正点1：SQLで school_name を取得するように変更
        $sql = "SELECT ld.book_id, ld.start_date, ld.end_date, bi.title, bi.author_name, sc.school_name";
        $sql .= " FROM lending_deny AS ld";
        $sql .= " INNER JOIN book_stack AS bs ON ld.book_id = bs.book_id";
        $sql .= " INNER JOIN book_info AS bi ON bs.isbn = bi.isbn";
        $sql .= " LEFT OUTER JOIN school AS sc ON bs.school_id = sc.school_id"; // ここで学校テーブルを結合
        $sql .= " WHERE ld.end_date >= NOW()";
        $sql .= " ORDER BY ld.end_date ASC";

        $stmt = $db->pdo->prepare($sql);
        $stmt->execute();
        $deny_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <title>貸出制限書籍リスト - 生徒用</title>
    <link rel="stylesheet" href="../css/librarian_myPage.css">
</head>
<body>
    <? if ($error_message != null): ?>
            <div style="color: red; padding: 20px; border: 1px solid red; background-color: #fee; text-align: center;">
                <h3>現在システムをご利用いただけません</h3>
                <p><?php echo h($error_message); ?></p>
            </div>
        <?php else: ?>
    <header class="main-header">
        <div class="header-logo">図書システム</div>
        <nav class="header-nav">
            <ul>
                <li><a href="stu_myPage.php">マイページへ戻る</a></li>
            </ul>
        </nav>
    </header>

    <div class="info-table-container">
        <h2>現在、貸出が制限されている書籍一覧</h2>
        <p>以下の書籍は、修理や特別展示などの理由により、一時的に貸出・予約ができません。</p>

        <div class="scroll-wrapper">
            <table class="info-table">
                <thead>
                    <tr>
                        <th>書籍ID</th>
                        <th>タイトル</th>
                        <th>著者</th>
                        <th>所蔵校</th> <th>貸出停止開始日</th>
                        <th>貸出再開予定日</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($deny_list)): ?>
                        <tr><td colspan="6">現在、貸出制限されている本はありません。</td></tr>
                    <?php else: ?>
                        <?php foreach ($deny_list as $row): ?>
                            <tr>
                                <td><?php echo h($row['book_id']); ?></td>
                                <td><?php echo h($row['title']); ?></td>
                                <td><?php echo h($row['author_name']); ?></td>
                                <td><?php echo h($row['school_name']); ?></td> <td><?php echo h(date('Y年m月d日', strtotime($row['start_date']))); ?></td>
                                <td style="color: #d9534f; font-weight:bold;">
                                    <?php echo h(date('Y年m月d日', strtotime($row['end_date']))); ?> まで
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
<?php endif; ?>
</html>