<?php
    // セッションの開始
    session_start();
    require_once '../db_connect.php';

    header("Cache-Control:no-cache,no-store,must-revalidate,max-age=0,post-check=0,pre-check=0");
    header("Pragma:no-cache");

    if (!isset($_SESSION['student_id'])) {
        // 司書としてログインしていない場合、ログインページへリダイレクト
        $_SESSION['message'] = "ログインしてください。";
        header("Location: student_login.php");
        exit();
    }

    //　ここからは、生徒としてログインしていないと実行されない
    $student_id = $_SESSION['student_id'];
    $school_name = null;
    $student_fullname;

    $student_fullname = null;
    if (isset($_SESSION['student_family_name']) && isset($_SESSION['student_family_name'])) {
        $student_fullname = $_SESSION['student_family_name'] . " " . $_SESSION['student_first_name']; 
    } else {
        $_SESSION['to_stu_myPage_message'] = "学生情報を正常に取得できませんでした。";
        header("Location: stu_myPage.php");
        exit();
    }


    // HTMLエスケープ関数
    function h($str) {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }

    // CSRFトークン発行関数
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
            echo "<tr><td colspan='6'>予約情報はありません。</td></tr>";
            return;
        }

        foreach($records as $rows) {
            $book_id = $rows['book_id'] ?? 'Error';
            $title = $rows['title'] ?? 'Error';
            $author_name = $rows['author_name'] ?? 'Error';
            $publisher = $rows['publisher'] ?? 'Error';
            $reservation_date = $rows['reservation_date'] ?? 'Error';
            $updated_date = $rows['updated_at'] ?? 'Error';

            $book_statusId = intval($rows['status_id']) ?? 'Error'; // 書籍状態ID
            $res_statusId = intval($rows['status_id']) ?? 'Error';  // 予約状態ID
            $res_statusName = $rows['status_name'] ?? 'Error';  // 予約状態名
            $display_resStatus = null;

            $alert_bkStatus = [4,7];// 4:予約受取待ち か 7:配送予約受取待ち

            if (in_array($book_statusId, $alert_bkStatus) && $res_statusId == 1) {
                $display_resStatus = "予約した本がカウンターに確保されました！";
            } else {
                $display_resStatus = $res_statusName;
            }

            $res_number = $rows['reservation_number'] ?? 'Error';

            // 予約番号をダイアログ
            $res_number_message = "alert('予約番号は". $res_number . "です。')";
                
            echo "<tr>";
            echo "<td>" . h($book_id) . "</td>";
            echo "<td><button onclick=". h($res_number_message) .">". "番号を表示" ."</button></td>";
            echo "<td>" . h($title) . "</td>";
            echo "<td>" . h($author_name) . "</td>";
            echo "<td>" . h($publisher) . "</td>";
            echo "<td>" . h($reservation_date) . "</td>";
            echo "<td>" . h($display_resStatus) . "</td>";
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

        //生徒の学校名を取得
        $get_schoolName_sql = "SELECT sc.school_name";
        $get_schoolName_sql .= " FROM student AS st";
        $get_schoolName_sql .= " LEFT OUTER JOIN school AS sc";
        $get_schoolName_sql .= " ON st.school_id = sc.school_id";
        $get_schoolName_sql .= " WHERE st.student_id = :student_id";
        $schoolName_stmt = $db->pdo->prepare($get_schoolName_sql);
        $schoolName_stmt->bindValue(':student_id', $student_id, PDO::PARAM_INT);
        $schoolName_stmt->execute();
        $fetch_record = $schoolName_stmt->fetch(PDO::FETCH_ASSOC);
        // 学校名代入
        $school_name = $fetch_record['school_name'] ?? 'Error';


        // テーブルデータ取得sql
        $sql = "SELECT bs.book_id, bs.status_id, bi.title, bi.author_name, bi.publisher, r.reservation_date, r.updated_at, rs.status_name, r.reservation_number";
        $sql .= " FROM reservation AS r";
        $sql .= " LEFT OUTER JOIN book_stack AS bs";
        $sql .= " ON r.book_id = bs.book_id";
        $sql .= " LEFT OUTER JOIN book_info AS bi";
        $sql .= " ON bs.isbn = bi.isbn";
        $sql .= " LEFT OUTER JOIN student AS s";
        $sql .= " ON r.student_id = s.student_id";
        $sql .= " LEFT OUTER JOIN reservation_status AS rs";
        $sql .= " ON r.status_id = rs.status_id";
        $sql .= " WHERE s.student_id = :student_id";
        $sql .= " AND r.reservation_date BETWEEN :lastYearDate AND :currentDate";
        $sql .= " ORDER BY r.reservation_date DESC";
        $stmt = $db->pdo->prepare($sql);
        $stmt->bindValue(':student_id', $student_id, PDO::PARAM_INT); // 自分のもの以外は予約履歴を表示できないようにする
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
    } finally {
        $db->connect();
    }

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>生徒用-予約参照ページ(<?php echo h($school_name); ?>)</title>
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
            <a href="student_reservation_reference.php">生徒用-予約参照ページ(<?php echo h($school_name); ?>)</a>
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
            <h2 ><?php echo h($student_fullname); ?>さんの予約履歴（過去一年分）</h2>

            <div class = "scroll-wrapper">
                <table class="info-table">
                    <thead>
                        <tr>
                            <th>書籍ID</th>
                            <th>予約番号</th>
                            <th>タイトル</th>
                            <th>著者</th>
                            <th>出版社</th>
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
            <button type="button" onclick="location.href='stu_myPage.php'">戻る</button>
        </div>

    </body>
</html>