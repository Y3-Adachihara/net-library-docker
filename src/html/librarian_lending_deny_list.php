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
    $error_message = null;

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
    <title>貸出拒否設定一覧(<?php echo h($school_name); ?>)</title>
    <link rel="stylesheet" href="../css/librarian_myPage.css">
    <style>
        .status-active { color: red; font-weight: bold; }
        .status-future { color: blue; font-weight: bold; }
        .status-ended { color: gray; }
    </style>
</head>
<body>
    <?php
        if (isset($_SESSION['message'])) {
            // h関数はファイル上部で定義済みと想定
            echo "<script>alert('" . h($_SESSION['message']) . "');</script>";
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
        <div class="header-logo">司書管理画面 - 貸出拒否設定</div>
        <nav class="header-nav">
            <ul>
                <li><a href="../html/librarian_myPage.php">マイページへ戻る</a></li>
            </ul>
        </nav>
    </header>

    <div class="info-table-container">
        <h2>貸出拒否（貸出停止）リスト</h2>

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
            <button type="button" onclick="location.href='../html/librarian_lending_deny_input.php'">新規設定追加</button>
        </div>
    </div>
</body>
<?php endif; ?>
</html>