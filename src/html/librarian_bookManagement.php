<?php
    require_once '../db_connect.php';
    session_start();

    if (!isset($_SESSION['librarian_id'])) {
        // 司書としてログインしていない場合、ログインページへリダイレクト
        $_SESSION['message'] = "司書としてログインしてください。";
        header("Location: librarian_login.php");
        exit();
    }

    //　ここからは、司書としてログインしていないと実行されない
    $_librarian_id = $_SESSION['librarian_id'];
    $_librarian_school_id = $_SESSION['librarian_school_id'];

    $local_reservations = [];
    $deliver_reservations = [];
    $all_reservations = [];

    /* コピペしたけど、これは確認画面でいい気がする
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
        //ここでトークンを隠し属性として送るためのhtmlコードを記述
        echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') . '">';
    }
        */

    // HTMLエスケープ関数
    function h($str) {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }

    function table_data_display(array $records, int $from_where = 0): void {
        if (empty($records)) {
            echo "<tr><td colspan='6'>現在、予約取り置きリストはありません。</td></tr>";
            return;
        }

        echo "<tr>";
        echo "<th>チェック</th>";
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

            // 自校の予約だった場合
            if ($from_where == 1) {
                echo "<td><input type=\"checkbox\" name=\"local_res[]\" value= \"" . h($book_id) . "\"></td>";    // これがチェックボックス
            } else if ($from_where == 2) {
                echo "<td><input type=\"checkbox\" name=\"deliver_res[]\" value= \"" . h($book_id) . "\"></td>";    // これがチェックボックス
            }
            echo "<td>" . h($book_id) . "</td>";
            echo "<td>" . h($book_isbn) . "</td>";
            echo "<td>" . h($book_title) . "</td>";
            echo "<td>" . h($student_school) . "</td>";
            echo "<td>" . h($full_name) . "</td>";
            echo "<td>" . h($reservation_date) . "</td>";
            echo "</tr>";
        
        }
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
        $sql_all .= " WHERE bs.school_id = :school_id";
        $sql_all .= " AND r.status_id = :res_status_id";
        $sql_all .= " AND bs.status_id = :bk_status_id";

        $stmt = $db->pdo->prepare($sql_all);
        $stmt->bindValue(':school_id', $_librarian_school_id, PDO::PARAM_INT);
        $stmt->bindValue(':res_status_id', 1, PDO::PARAM_INT);
        $stmt->bindValue(':bk_status_id', 3, PDO::PARAM_INT);
        $stmt->execute();
        $toMySchoolReservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($toMySchoolReservations as $rows) {
            $all_reservations [] = $rows;   // とりま自校・他校からの予約リストは入れる

            // 自校からの予約リストを格納
            if (intval($rows['school_id']) == $_librarian_school_id) {
                $local_reservations [] = $rows;

            // 他校からの予約リストを格納
            } else {
                $deliver_reservations [] = $rows;
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
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>司書用マイページ(<?php echo h($school_name); ?>)</title>
    <link rel="stylesheet" href="../css/librarian_myPage.css">
</head>
<body>
    <div class="tabs">
        <button onclick="showTab('all')">① 全リスト (回収用)</button>
        <button onclick="showTab('local')">② 自校予約 (棚へ)</button>
        <button onclick="showTab('delivery')">③ 他校配送 (箱へ)</button>
    </div>

    <div id="area-all" class="tab-content">
        <p>棚から以下の本をすべて回収してください。</p>
        <table>
            <?php table_data_display($all_reservations) ?>
        </table>
    </div>

    <div id="area-local" class="tab-content" style="display:none;">
        <form action="librarian_book_confirm.php" method="post">
            <p>自校の生徒への予約です。チェックして確認画面へ進んでください。</p>
            <table>
                <?php table_data_display($local_reservations, 1) ?>
            </table>
            <input type="hidden" name="next_status" value="4">
            <button type="submit">確認画面へ</button>
        </form>
    </div>

    <div id="area-delivery" class="tab-content" style="display:none;">
        <form action="librarian_book_confirm.php" method="post">
            <p>他校への配送です。チェックして確認画面へ進んでください。</p>
            <table>
                <?php table_data_display($deliver_reservations, 2) ?>
            </table>
            <input type="hidden" name="next_status" value="5">
            <button type="submit">確認画面へ</button>
        </form>
    </div>

    <script>
    function showTab(tabName) {
        // 1. 一旦全部隠す
        document.getElementById('area-all').style.display = 'none';
        document.getElementById('area-local').style.display = 'none';
        document.getElementById('area-delivery').style.display = 'none';
        
        // 2. 選ばれたやつだけ表示
        document.getElementById('area-' + tabName).style.display = 'block';
    }
    </script>

    <div class="button-row">
        <button type="button" class="btn" onclick="location.href='../html/librarian_myPage.php'">戻る</button>
    </div>
</body>
</html>