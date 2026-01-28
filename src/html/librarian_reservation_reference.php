<?php
    // セッションの開始
    session_start();
    require_once '../db_connect.php';

    header("Cache-Control:no-cache,no-store,must-revalidate,max-age=0,post-check=0,pre-check=0");
    header("Pragma:no-cache");

    if (!isset($_SESSION['librarian_id'])) {
        // 司書としてログインしていない場合、ログインページへリダイレクト
        $_SESSION['message'] = "司書としてログインしてください。";
        header("Location: librarian_login.php");
        exit();
    }

    //　ここからは、司書としてログインしていないと実行されない
    $_librarian_id = $_SESSION['librarian_id'];

    // HTMLエスケープ関数
    function h($str) {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
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
        //ここでトークンを隠し属性として送るためのhtmlコードを記述
        echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') . '">';
    }

    // テーブルデータ表示関数
    function table_data_display(array $records): void {

        if (empty($records)) {
            echo "<tr><td colspan='6'>該当する貸出情報はありません。</td></tr>";
            return;
        }

        foreach($records as $rows) {
            $book_id = $rows['book_id'] ?? 'Error';
            $title = $rows['title'] ?? 'Error';
            $belong_id = $rows['grade'] . "年" . $rows['class'] . "組" . $rows['number'] . "番"  ?? 'Error';
            $family_name = $rows['family_name'] ?? 'Error';
            $first_name = $rows['first_name'] ?? 'Error';
             //苗字と名前は別れているため、フルネームを作成
            $full_name = $family_name . " " . $first_name;

            $reservation_date = $rows['reservation_date'] ?? 'Error';
            $updated_date = $rows['updated_at'] ?? 'Error';
            $status_name = $rows['status_name'] ?? 'Error';
                
            echo "<tr>";
            echo "<td>" . h($book_id) . "</td>";
            echo "<td>" . h($title) . "</td>";
            echo "<td>" . h($belong_id) . "</td>";
            echo "<td>" . h($full_name) . "</td>";
            echo "<td>" . h($reservation_date) . "</td>";
            echo "<td>" . h($status_name) . "</td>";
            echo "<td>" . h($updated_date) . "</td>";
            echo "</tr>";
        }
    }

    //現在の日付を取得(クエリ用)
    $current_date = new DateTimeImmutable();
    $current_date_str = $current_date->format('Y-m-d H:i:s');
        
    //1年前の日付を取得（クエリ用）
    $lastYear_date = $current_date->modify('-2 year');
    $lastYear_date_str = $lastYear_date->format('Y-m-d H:i:s');

    $fetchAll_record = [];  //結果が無かった場合に備えて初期化
        
    try {
        $db = new db_connect();
        $db->connect();

        //司書の学校IDと学校名を取得
        $get_librarian_school_sql = "SELECT lib.school_id, sch.school_name FROM librarian AS lib";
        $get_librarian_school_sql .= " LEFT OUTER JOIN school AS sch ON lib.school_id = sch.school_id";
        $get_librarian_school_sql .= " WHERE lib.librarian_id = :librarian_id;";
        $stmt = $db->pdo->prepare($get_librarian_school_sql);
        $stmt->bindValue(':librarian_id', $_librarian_id, PDO::PARAM_INT);
        $stmt->execute();
        $librarian_school_id = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$librarian_school_id) {
            $_SESSION['res_refer_error'] = "司書の学校情報取得に失敗しました。";
            header("Location: librarian_myPage.php");
            exit();
        }

        $school_id = intval($librarian_school_id['school_id']);
        $school_name = $librarian_school_id['school_name'];

        // テーブルデータ取得sql
        $sql = "SELECT bs.book_id, bi.title, s.grade, s.class, s.number, s.family_name, s.first_name,";
        $sql .= " r.reservation_date, r.updated_at, rs.status_name";
        $sql .= " FROM reservation AS r";
        $sql .= " LEFT OUTER JOIN book_stack AS bs";
        $sql .= " ON r.book_id = bs.book_id";
        $sql .= " LEFT OUTER JOIN book_info AS bi";
        $sql .= " ON bs.isbn = bi.isbn";
        $sql .= " LEFT OUTER JOIN student AS s";
        $sql .= " ON r.student_id = s.student_id";
        $sql .= " LEFT OUTER JOIN reservation_status AS rs";
        $sql .= " ON r.status_id = rs.status_id";
        $sql .= " WHERE s.school_id = :school_id";
        $sql .= " AND r.reservation_date BETWEEN :lastYearDate AND :currentDate";
        $sql .= " ORDER BY r.reservation_date DESC";
        $stmt = $db->pdo->prepare($sql);
        $stmt->bindValue(':school_id', $school_id, PDO::PARAM_INT); // 自校以外は予約履歴を表示できないようにする
        $stmt->bindValue(':lastYearDate', $lastYear_date_str, PDO::PARAM_STR);
        $stmt->bindValue(':currentDate', $current_date_str, PDO::PARAM_STR);
        $stmt->execute();
        $fetchAll_record = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
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
    <title>司書用-予約参照ページ(<?php echo h($school_name); ?>)</title>
    <link rel="stylesheet" href="../css/librarian_myPage.css">
</head>
<script>
    function confirmLogout() {
        if(window.confirm('本当にログアウトしますか？')) {
            document.link_logoutFORM.submit();
        }
    }
</script>
    <body>
        <header class="main-header">
        <div class="header-logo">
            <a href="librarian_reservation_reference.php">司書用-予約参照ページ(<?php echo h($school_name); ?>)</a>
        </div>
        <nav class="header-nav">
            <ul>
                <li><a href="#" onclick="alert('余裕があったら、マイページの使い方やヘルプ等を説明するページを作ってもいいかも？'); return false;">はじめての方へ</a></li>
                <li><a href="#" onclick = "confirmLogout(); return false;">ログアウト</a></li>
            </ul>
        </nav>
        </header>

        <!-- ログアウトボタンを押したときのCSFSトークン発行 -->
        <form method="POST" action = "../php/logout.php" name = "link_logoutFORM">
            <?php 
                set_csrf_token($csrf_token);
            ?>
            <input type="hidden" name = "page_id" value= "1">
        </form>

        <div class ="info-table-container">
            <h2 >本校の書籍に対する予約状況</h2>

            <div class = "scroll-wrapper">
                <table class="info-table">
                    <thead>
                        <tr>
                            <th>書籍ID</th>
                            <th>タイトル</th>
                            <th>所属</th>
                            <th>予約者名</th>
                            <th>予約日</th>
                            <th>予約ステータス</th>
                            <th>予約ステータス更新日</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                            table_data_display($fetchAll_record);
                        ?>
                    </tbody>

                </table>
            </div>
        </div>

        <div class="librarian-menu-form">
            <button type="button" onclick="location.href='librarian_myPage.php'">戻る</button>
        </div>

    </body>
</html>