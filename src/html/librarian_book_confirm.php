<?php
    require_once '../db_connect.php';
    session_start();

    if (!isset($_SESSION['librarian_id'])) {
        // 司書としてログインしていない場合、ログインページへリダイレクト
        $_SESSION['message'] = "司書としてログインしてください。";
        header("Location: librarian_login.php");
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

    $book_id = $_POST['local_res'] ?? $_POST['deliver_res'] ?? [];   // 自校からの予約リスト
    $next_status = $_POST['next_status'] ?? null;

    $local_selected_res = null;
    $deliver_selected_res = null;

    // HTMLエスケープ関数
    function h($str) {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }

    function table_data_display(array $records, int $next_status): void {

        if (empty($records)) {
            echo "<tr><td colspan='6'>選択された予約引当リストはありません</td></tr>";
            return;
        }

        if ($next_status == 4) {
            echo "<p>以下の予約済みの書籍の状態を「4:予約受取待ち」に変更します。これらの本を予約受取待ち置き場に置いたことを確認してください。</p>";
        } else if ($next_status == 5) {
            echo "<p>以下の予約済みの書籍の状態を「5:配送待ち（予約配送）」に変更します。これらの本を配送待ちボックスに置いたことを確認してください。</p>";
        }

        echo "<tr>";
        echo "<th>書籍ID</th>";
        echo "<th>ISBN</th>";
        echo "<th>タイトル</th>";
        echo "<th>所属学校</th>";
        echo "<th>予約学生</th>";
        echo "<th>予約日</th>";
        echo "</tr>";

        foreach($records as $rows) {
            $book_id = $rows['book_id'];
            $book_isbn = $rows['isbn'];
            $book_title = $rows['title'];
            $student_school = $rows['school_name'];
            $family_name = $rows['family_name'];
            $first_name = $rows['first_name'];
            $full_name = $family_name . " " . $first_name;
            $reservation_date = $rows['reservation_date'];

            echo "<tr>";
            echo "<td>" . h($book_id) . "</td>";
            echo "<td>" . h($book_isbn) . "</td>";
            echo "<td>" . h($book_title) . "</td>";
            echo "<td>" . h($student_school) . "</td>";
            echo "<td>" . h($full_name) . "</td>";
            echo "<td>" . h($reservation_date) . "</td>";
            echo "</tr>";
        
        }
    }
?>

<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>予約取り置き確認画面(<?php echo h($school_name); ?>)</title>
    <link rel="stylesheet" href="../css/librarian_myPage.css">
</head>
<body>
    <?php
        if ($next_status == 4) {
            table_data_display($local_selected_res, $next_status);
        } else if ($next_status == 5) {
            table_data_display($deliver_selected_res, $next_status);
        } else {
            $_SESSION['book_manageConfirm_message'] = "不正なリクエストです。";
            header("Location: librarian_bookManagement.php");
            exit();
        }
    ?>
    <button onclick="location.href='../html/librarian_bookManagement.php'">戻る</button>
    <form>
        <button type="submit">確定</button>
    </form>
</body>