<?php
    session_start();
    require_once '../db_connect.php';

    if (!isset($_SESSION['librarian_id'])) {
        $_SESSION['message'] = "司書としてログインしてください。";
        header("Location: librarian_login.php");
        exit();
    }

    $school_name = $_SESSION['school_name'] ?? '所属校不明';

    function h($str) {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }

    $deny_list = [];

    try {
        $db = new db_connect();
        $db->connect();

        // ★修正点1：SQLで school_name を取得
        $sql = "SELECT ld.*, bi.title, bi.isbn, sc.school_name";
        $sql .= " FROM lending_deny AS ld";
        $sql .= " INNER JOIN book_stack AS bs ON ld.book_id = bs.book_id";
        $sql .= " INNER JOIN book_info AS bi ON bs.isbn = bi.isbn";
        $sql .= " LEFT OUTER JOIN school AS sc ON bs.school_id = sc.school_id"; // 追加
        $sql .= " ORDER BY ld.updated_at DESC";

        $stmt = $db->pdo->prepare($sql);
        $stmt->execute();
        $deny_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        $error_message = "エラー：" . $e->getMessage();
    }
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>貸出拒否設定一覧(<?php echo h($school_name); ?>)</title>
    <link rel="stylesheet" href="../css/librarian_myPage.css">
    <style>
        .status-active { color: red; font-weight: bold; }
        .status-future { color: blue; font-weight: bold; }
        .status-ended { color: gray; }
    </style>
</head>
<body>
    <header class="main-header">
        <div class="header-logo">司書管理画面 - 貸出拒否設定</div>
        <nav class="header-nav">
            <ul>
                <li><a href="../html/librarian_myPage.php">マイページへ戻る</a></li>
            </ul>
        </nav>
    </header>

    <div class="info-table-container">
        <h2>貸出拒否（貸出停止）リスト</h2>
        
        <?php if (isset($error_message)): ?>
            <p style="color:red;"><?php echo h($error_message); ?></p>
        <?php endif; ?>

        <div class="scroll-wrapper">
            <table class="info-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Pass Num</th>
                        <th>書籍ID</th>
                        <th>タイトル</th>
                        <th>所蔵校</th> <th>開始日時</th>
                        <th>終了日時</th>
                        <th>状態</th>
                        <th>更新日時</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($deny_list)): ?>
                        <tr><td colspan="9">データはありません。</td></tr>
                    <?php else: ?>
                        <?php 
                            $now = new DateTime();
                            foreach ($deny_list as $row): 
                                $start = new DateTime($row['start_date']);
                                $end = new DateTime($row['end_date']);
                                
                                $status = "";
                                $class = "";
                                if ($now < $start) {
                                    $status = "開始前";
                                    $class = "status-future";
                                } elseif ($now >= $start && $now <= $end) {
                                    $status = "停止中";
                                    $class = "status-active";
                                } else {
                                    $status = "終了";
                                    $class = "status-ended";
                                }
                        ?>
                            <tr>
                                <td><?php echo h($row['deny_id']); ?></td>
                                <td><?php echo h($row['pass_num']); ?></td>
                                <td><?php echo h($row['book_id']); ?></td>
                                <td><?php echo h($row['title']); ?></td>
                                <td><?php echo h($row['school_name']); ?></td> <td><?php echo h($start->format('Y/m/d H:i')); ?></td>
                                <td><?php echo h($end->format('Y/m/d H:i')); ?></td>
                                <td class="<?php echo $class; ?>"><?php echo $status; ?></td>
                                <td><?php echo h($row['updated_at']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="librarian-menu-form">
            <button type="button" onclick="alert('新規登録画面へ遷移（未実装）')">新規設定追加</button>
        </div>
    </div>
</body>
</html>