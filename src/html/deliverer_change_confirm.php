<?php
    session_start();

    if (!isset($_SESSION['deliverer_id'])) {
        // 配送員としてログインしていない場合、ログインページへリダイレクト
        $_SESSION['message'] = "配送員としてログインしてください。";
        header("Location: deliverer_login.php");
        exit();
    }
    
    // CSRFトークン発行関数(発行するだけで、セッション変数への保存は行わないから注意！)
    function csrf_token_generate(): string {
        $toke_byte = random_bytes(16);
        $csrf_token = bin2hex($toke_byte);
        return $csrf_token;
    }
    // CSRFトークンの生成
    $csrf_token = csrf_token_generate();

    // CSRFトークンセット関数
    function set_csrf_token(String $csrf_token): void {
        // CSRF対策用のトークンをセッションに保存
        $_SESSION['csrf_token'] = $csrf_token;
        echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') . '">';
    }
    
    //CSRFトークン(書籍状態変更の前に)
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = csrf_token_generate();
    }
    $csrf_token = $_SESSION['csrf_token'];

    // ↓これは選択されたbook_idの配列
    $carry_in_list = $_POST['carry_in_list'] ?? []; // 搬入リスト(書籍IDのリスト)
    $carry_out_list =  $_POST['carry_out_list'] ?? [];   // 搬出リスト（書籍IDのリスト）
    $next_status = $_POST['next_status'] ?? null;

    $selected_books = null; // 最終的に送る選択された書籍IDリスト（他校からのみ、もしくは自校からのみのどちらかになる）
    if ($next_status == 15) {
        $selected_books = $carry_in_list;
    } else if ($next_status == 13) {
        $selected_books = $carry_out_list;
    }

    // 何も選ばれていなかったら戻す
    if (empty($selected_books)) {
        $_SESSION['book_manageConfirm_message'] = "書籍リストを選択してください。";
        header("Location: librarian_bookManage.php");
        exit();
    }

    // IN句に指定する選択された本のbook_idの配列
    $inClause = substr(str_repeat(',?', count($selected_books)), 1);

    $carry_in_list_selected = null;
    $carry_out_list_selected = null;

    // HTMLエスケープ関数
    function h($str) {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }

    function table_data_display(array $records, int $next_status): void {
        if (empty($records)) {
            echo "<tr><td colspan='6'>選択された予約引当リストはありません</td></tr>";
            return;
        }

        // 搬入だった場合
        if ($next_status == 15) {
            echo "<p>これらのリストを搬入します</p>";

        // 他校の予約だった場合
        } else if ($next_status == 13) {
            echo "<p>これらのリストを搬出します</p>";
        }

        echo "<table>";

        echo "<tr>";
        echo "<th>書籍ID</th>";
        echo "<th>ISBN</th>";
        echo "<th>タイトル</th>";
        echo "<th>出版社</th>";
        echo "<th>送り元</th>";
        echo "<th>宛先</th>";
        echo "</tr>";
        foreach ($records as $row) {
            list($book_id, $book_isbn, $book_title, $book_publisher, $from_school, $to_school) = explode('|', $row);

            echo "<tr>";
            echo "<td>" . h($book_id) . "</td>";
            echo "<td>" . h($book_isbn) . "</td>";
            echo "<td>" . h($book_title) . "</td>";
            echo "<td>" . h($book_publisher) . "</td>";
            echo "<td>" . h($from_school) . "</td>";
            echo "<td>" . h($to_school) . "</td>";
            echo "</tr>";
        }

    }


?>

<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>配送確認画面(<?php echo h($school_name); ?>)</title>
    <link rel="stylesheet" href="../css/librarian_myPage.css">
</head>
<body>
    <?php
        if ($next_status == 15) {
            table_data_display($carry_in_list, $next_status);
        } else if ($next_status == 13) {
            table_data_display($carry_out_list, $next_status);
        } else {
            $_SESSION['book_manageConfirm_message'] = "不正なリクエストです。";
            header("Location: librarian_bookManagement.php");
            exit();
        }
    ?>
    <button onclick="location.href='../html/deliverer_book_management.php'">戻る</button>
    <form action="../php/change_delBook_status.php" method="POST">
        <!-- CSRFトークンを隠し属性にセット -->
        <?php set_csrf_token($csrf_token); ?>

        <!-- 選択された書籍IDの配列を、配列として引き渡す -->
        <?php foreach($selected_books as $book_id): ?>
            <input type="hidden" name="book_ids[]" value="<?php echo h($book_id); ?>">
        <?php endforeach; ?>
        <input type="hidden" name="next_status" value="<?php echo h($next_status); ?>">
        <button type="submit">確定</button>
    </form>
</body>