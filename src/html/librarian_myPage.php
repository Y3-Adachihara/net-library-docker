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

    // HTMLエスケープ関数
    function h($str) {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }

    // テーブルデータ表示関数
    function table_data_display(array $records): void {

        if (empty($records)) {
            echo "<tr><td colspan='6'>該当する貸出情報はありません。</td></tr>";
            return;
        }

        foreach($records as $rows) {
            $lending_id = $rows['book_id'];
            $title = $rows['title'];
            $belong_id = $rows['grade'] . "年" . $rows['class'] . "組" . $rows['number'] . "番";
            $family_name = $rows['family_name'];
            $first_name = $rows['first_name'];
            $lending_date = $rows['lending_date'];
            $return_date = $rows['return_date'];

            // その生徒が借りた（返した）書籍の書籍状態IDを取得
            $book_status_id= $rows['status_id'];
            $status_name = $rows['status_name'];

            // 貸出可能の時だけ返却済みとする
            if ($book_status_id == 1) {
                $status_name = '返却済み';
            }

            //苗字と名前は別れているため、フルネームを作成
            $full_name = $family_name . " " . $first_name;
                
            echo "<tr>";
            echo "<td>" . h($lending_id) . "</td>";
            echo "<td>" . h($title) . "</td>";
            echo "<td>" . h($belong_id) . "</td>";
            echo "<td>" . h($full_name) . "</td>";
            echo "<td>" . h($lending_date) . "</td>";
            if (is_null($return_date)) {
                echo "<td>未返却</td>";
            } else {
                echo "<td>" . h($return_date) . "</td>";
            }
            echo "<td>" . h($status_name) . "</td>";
            echo "</tr>";
        }
    }

    //現在の日付を取得
    $current_date = new DateTimeImmutable();
    $current_date_str = $current_date->format('Y-m-d H:i:s');
        
    //1年前の日付を取得
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
        $school_id = intval($librarian_school_id['school_id']);
        $school_name = $librarian_school_id['school_name'];


        //結合するテーブル（書籍テーブル、学生テーブル, 書籍テーブル、書籍状態テーブル、貸出テーブル、学校テーブル）から、過去1年間の貸出情報を取得
        $sql = "SELECT b_sc.book_id, b_if.title, stu.grade, stu.class, stu.number, stu.family_name, stu.first_name, l.lending_date, l.return_date, b_st.status_id, b_st.status_name";
        $sql .= " FROM lending AS l";
        $sql .= " LEFT OUTER JOIN book_stack AS b_sc";
        $sql .= " ON l.book_id = b_sc.book_id";
        $sql .= " LEFT OUTER JOIN book_info AS b_if";
        $sql .= " ON b_sc.isbn = b_if.isbn";
        $sql .= " LEFT OUTER JOIN student AS stu";
        $sql .= " ON l.student_id = stu.student_id";
        $sql .= " LEFT OUTER JOIN book_status AS b_st";
        $sql .= " ON b_sc.status_id = b_st.status_id";
        $sql .= " WHERE l.lending_date BETWEEN :lastYearDate AND :currentDate";
        $sql .= " AND stu.school_id = :school_id";
        $sql .= " ORDER BY l.lending_date DESC;";

        $stmt = $db->pdo->prepare($sql);
        $stmt->bindValue(':lastYearDate', $lastYear_date_str, PDO::PARAM_STR);
        $stmt->bindValue(':currentDate', $current_date_str, PDO::PARAM_STR);
        $stmt->bindValue(':school_id', $school_id, PDO::PARAM_INT);
        $stmt->execute();
        $fetchAll_record= $stmt->fetchAll(PDO::FETCH_ASSOC);
            
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
            <a href="librarian_myPage.php">司書用Myページ(<?php echo h($school_name); ?>)</a>
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

        <form method="POST" class="librarian-menu-form">
            <?php 
                set_csrf_token($csrf_token);
            ?>
            <button type="submit" formaction="../html/検索画面.html" class="add-book-button">書籍検索</button>
            <button type="submit" formaction="../html/貸出返却.php" class="manage-users-button">貸出・返却</button>
            <button type="submit" formaction="../html/librarian_manageUsers.php" class="manage-users-button">予約状況参照</button>
            <button type="submit" formaction="../html/書籍登録.html" class="add-book-button">新規書籍登録</button>
        </form>

        <div class ="info-table-container">
            <h2 >現在の貸出状況</h2>

            <div class = "scroll-wrapper">
                <table class="info-table">
                    <thead>
                        <tr>
                            <th>書籍ID</th> <!-- 貸出IDから変更 -->
                            <th>タイトル</th>
                            <th>所属</th>
                            <th>貸出者名</th>
                            <th>貸出日</th>
                            <th>返却日</th>
                            <th>状態</th>   <!-- 貸出した学生の状態を記述するように変更 -->
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

    </body>
</html>