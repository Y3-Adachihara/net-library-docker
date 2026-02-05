<?php
    require_once '../db_connect.php';
    session_start();

    if (!isset($_SESSION['librarian_id'])) {
        // 司書としてログインしていない場合、ログインページへリダイレクト
        $_SESSION['message'] = "司書としてログインしてください。";
        header("Location: librarian_login.php");
        exit();
    }

    // メッセージ
    if (isset($_SESSION['bookStatus_changeResult_message'])) {
        $message = $_SESSION['book_manageConfirm_message'];
        echo "<script>alert('" . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . "');</script>";
        unset($_SESSION['book_manageConfirm_message']);
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
    
    // 
    $librarian_school_id = $_SESSION['librarian_school_id'];

    // ↓これは選択されたbook_idの配列
    $local_res = $_POST['local_res'] ?? []; // 自校からの予約リスト(書籍IDのリスト)
    $deliver_res =  $_POST['deliver_res'] ?? [];   // 他校からの予約リスト（書籍IDのリスト）
    $next_status = $_POST['next_status'] ?? null;

    $selected_books = null; // 最終的に送る選択された書籍IDリスト（他校からのみ、もしくは自校からのみのどちらかになる）
    if ($next_status == 4) {
        $selected_books = $local_res;
    } else if ($next_status == 5) {
        $selected_books = $deliver_res;
    }

    // IN句に指定する選択された本のbook_idの配列
    $inClause = substr(str_repeat(',?', count($selected_books)), 1);

    // 何も選ばれていなかったら戻す
    if (empty($selected_books)) {
        $_SESSION['book_manageConfirm_message'] = "書籍リストを選択してください。";
        header("Location: librarian_bookManage.php");
        exit();
    }

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
        echo "<table>";

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
        echo "<table>";
        
    }
    
    try {
        $db = new db_connect();
        $db->connect();

        // 自校・他校からの予約
        $sql_all = "SELECT r.book_id, r.reservation_date, bi.title, bi.isbn, s.school_id, sc.school_name, s.family_name, s.first_name";
        $sql_all .= " FROM reservation AS r";
        $sql_all .= " LEFT OUTER JOIN book_stack AS bs";
        $sql_all .= " ON r.book_id = bs.book_id";
        $sql_all .= " LEFT OUTER JOIN book_info AS bi";
        $sql_all .= " ON bs.isbn = bi.isbn";
        $sql_all .= " LEFT OUTER JOIN student AS s";
        $sql_all .= " ON r.student_id = s.student_id";
        $sql_all .= " LEFT OUTER JOIN school AS sc";
        $sql_all .= " ON s.school_id = sc.school_id";
        $sql_all .= " WHERE bs.book_id IN ($inClause)";
        $stmt = $db->pdo->prepare($sql_all);
        $stmt->execute($selected_books);
        $toMySchoolReservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($toMySchoolReservations as $row) {
            if (intval($row['school_id']) == $librarian_school_id) {
                $local_selected_res [] = $row;
            } else {
                $deliver_selected_res [] = $row;
            }
        }
    } catch (PDOException $e) {
        $error_message = "データの取得に失敗しました。" . $e->getMessage();
        echo "<script>alert('" . htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8') . "');</script>";
    } catch (Exception $e) {
        $error_message = "予期せぬエラーが発生しました。" . $e->getMessage();
        echo "<script>alert('" . htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8') . "');</script>";
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
    <form action="../php/change_resBook_status.php" method="POST">
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