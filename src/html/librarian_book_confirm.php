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
    
    $librarian_id = $_SESSION['librarian_id'] ?? null;
    $librarian_school_id = $_SESSION['librarian_school_id'] ?? null;
    $librarian_fullname = '';
    $librarian_school_name = '';

    // ↓これは選択されたbook_idの配列
    $local_noncarry_res = $_POST['local_noncarry_res'] ?? []; // 自校からの予約リスト(書籍IDのリスト)
    $local_carry_res = $_POST['local_carry_res'] ?? []; // 自校からの予約リスト(書籍IDのリスト)
    $deliver_res =  $_POST['deliver_res'] ?? [];   // 他校からの予約リスト（書籍IDのリスト）
    $next_status = $_POST['next_status'] ?? null;

    $selected_books = null;
    if ($next_status == 4) {
        $selected_books = $local_noncarry_res; // 自校からの予約、本は自校所蔵
    } else if ($next_status == 7) {
        $selected_books = $local_carry_res;  // 自校からの予約、本は他校所蔵
    } else if ($next_status == 5) {
        $selected_books = $deliver_res; // 他校からの予約
    }

    // IN句に指定する選択された本のbook_idの配列
    $inClause = substr(str_repeat(',?', count($selected_books)), 1);

    // 何も選ばれていなかったら戻す
    if (empty($selected_books)) {
        $_SESSION['book_manageConfirm_message'] = "書籍リストを選択してください。";
        header("Location: librarian_bookManagement.php");
        exit();
    }

    //book_manageConfirm_message
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
            echo "<p>以下の予約済みの書籍の状態を「4:予約受取待ち」に変更します。これらの本を予約取り置き場に置いたことを確認してください。</p>";
        } else if ($next_status == 7) {
            echo "<p>以下の予約済みの書籍の状態を「7:配送予約受取待ち」に変更します。これらの本を予約取り置き場に置いたことを確認してください。</p>";
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

    $error_message = null;
    
    try {
        $db = new db_connect();
        $db->connect();

        $sql = "SELECT librarian.family_name, librarian.first_name, school.school_name FROM librarian LEFT JOIN school ON librarian.school_id = school.school_id WHERE librarian_id = ?";
        $stmt = $db->pdo->prepare($sql);
        $stmt->execute([$_SESSION['librarian_id']]);
        $librarian = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($librarian) {
            $librarian_fullname = $librarian['family_name'] . ' ' . $librarian['first_name'];
            $librarian_school_name = $librarian['school_name'];
        }

        $sql = "SELECT * FROM school";
        $stmt = $db->pdo->prepare($sql);
        $stmt->execute();
        $schools = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($schools) {
            foreach ($schools as $school) {
                if ($school['school_id'] == $librarian_school_id) {
                    $librarian_school_name = $school['school_name'];
                    break;
                }
            }
        }

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
        $sql_all .= " AND r.status_id = 1";
        $sql_all .= " AND r.reservation_id = (
                        SELECT r2.reservation_id
                        FROM reservation AS r2
                        WHERE r2.book_id = r.book_id
                        AND r2.status_id = 1
                        ORDER BY r2.reservation_date ASC, r2.reservation_id ASC
                        LIMIT 1
                      )";
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
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>予約取り置き確認画面(<?php echo h($school_name); ?>)</title>
    <link rel="stylesheet" href="../css/librarian_book_confirm.css">
</head>
<body>
    <?php if ($error_message !== null): ?>
        <div style="color: red; padding: 20px; border: 1px solid red; background-color: #fee; text-align: center;">
                <h3>現在システムをご利用いただけません</h3>
                <p><?php echo h($error_message); ?></p>
            </div>
    <?php else: ?>
    <header>
        <a href="#"><?php echo htmlspecialchars($librarian_fullname); ?> さん(<?php echo htmlspecialchars($librarian_school_name); ?>)の検索画面</a>
        <button class="logout-btn" onclick="confirmLogout()">ログアウト</button>
    </header>
    <!-- ログアウトボタンを押したときのCSFSトークン発行 -->
        <form method="POST" action = "../php/logout.php" name = "link_logoutFORM">
            <?php
                set_csrf_token($csrf_token);
            ?>
            <input type="hidden" name = "page_id" value= "1">
        </form>
    <?php
        if ($next_status == 4) {
            table_data_display($local_selected_res, $next_status);
        } else if ($next_status == 5) {
            table_data_display($deliver_selected_res, $next_status);
        } else if ($next_status == 7) {
            table_data_display($local_selected_res, $next_status);
        }
    ?>
    <div class="button-group">
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
    </div>
</body>
<?php endif; ?>
<script>
    function confirmLogout() {
            if (confirm("ログアウトしますか？")) {
                document.getElementById('logout_form').submit();
            }
        }
</script>