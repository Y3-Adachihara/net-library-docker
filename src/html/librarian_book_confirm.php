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

    $local_selected_res [] = $_POST['local_res'];   // 自校からの予約リスト
    $deliver_selected_res [] = $_POST['deliver_res'];   // 他校からの予約リスト

    function table_data_display(array $records, int $from_where = 0): void {
        if (empty($records)) {
            echo "<tr><td colspan='6'>選択された予約引当リストはありません</td></tr>";
            return;
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

            // 自校の予約だった場合
            if ($from_where == 1) {
                echo "<td><input type=\"checkbox\" name=\"local_res[]\" value= \"" . h($book_id) . "\"></td>";    // これがチェックボックス
            } else if ($from_where = 2) {
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



?>