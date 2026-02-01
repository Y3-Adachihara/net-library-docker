<?php
    require_once '../db_connect.php';
    session_start();
    
    if (!isset($_SESSION['deliverer_id'])) {
        // 配送員としてログインしていない場合、ログインページへリダイレクト
        $_SESSION['message'] = "配送員としてログインしてください。";
        header("Location: deliverer_login.php");
        exit();
    }

    // 確認画面に来た時に何も選択されていないときのメッセージ
    if (isset($_SESSION['book_manageConfirm_message'])) {
        $message = $_SESSION['book_manageConfirm_message'];
        echo "<script>alert('" . h($message) . "');</script>";
        unset($_SESSION['book_manageConfirm_message']);
    }

    //　ここからは、配送員としてログインしていないと実行されない
    $deliverer_family_name = $_SESSION['deliverer_family_name'] ?? '';
    $deliverer_first_name = $_SESSION['deliverer_first_name'] ?? '';
    $deliverer_full_name = $deliverer_family_name . " " . $deliverer_first_name ?? '';

    $unload_list = [];  // 荷下ろし（配送してきた本）
    $pickup_list = []; // 集荷（これから配送する本）
    $selected_school_id = null;
    if(isset($_POST['selected_school_id'])) {
        $selected_school_id = intval($_POST['selected_school_id']);
    }

    $selected_schools = [];    // 現在地の学校リスト
    $carry_in_list = []; // 搬入リスト
    $carry_out_list = []; // 搬出リスト

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

    // 学校が選ばれなかったときのメッセージ
    $error_message = $_SESSION['dbm_school_selected_result'] ?? null;
    if (isset($_SESSION['dbm_school_selected_result'])) {
        echo "<script>alert('" . h($error_message) . "');</script>";
        unset($_SESSION['dbm_school_selected_result']);
    }

    function table_data_display(array $records, int $delivery_type) {   // 1:搬入 2:搬出
        if (empty($records)) {
    
            if ($delivery_type == 1) {
                echo "<tr><td colspan='6'>現在、搬入リストはありません。</td></tr>";
                return;
            } else if ($delivery_type == 2) {
                echo "<tr><td colspan='6'>現在、搬出リストはありません。</td></tr>";
                return;
            }
            
        }

        echo "<tr>";
        echo "<th>チェック</th>";
        echo "<th>書籍ID</th>";
        echo "<th>ISBN</th>";
        echo "<th>タイトル</th>";
        echo "<th>出版社</th>";
        echo "<th>送り元</th>";
        echo "<th>宛先</th>";
        echo "</tr>";

        foreach ($records as $rows) {
            $book_id = $rows['book_id'];
            $book_isbn = $rows['isbn'];
            $book_title = $rows['title'];
            $book_publisher = $rows['publisher'];
            $from_school = $rows['departure_school_name'];
            $to_school = $rows['destination_school_name'];

            echo "<tr>";

            // 搬入だった場合
            if ($delivery_type == 1) {
                echo "<td><input type=\"checkbox\" name=\"carry_in_list[]\" value= \"" . h($book_id) . "\"></td>";    // これがチェックボックス

            // 他校の予約だった場合
            } else if ($delivery_type == 2) {
                echo "<td><input type=\"checkbox\" name=\"carry_out_list[]\" value= \"" . h($book_id) . "\"></td>";    // これがチェックボックス
            }

            echo "<td>" . h($book_id) . "</td>";
            echo "<td>" . h($book_isbn) . "</td>";
            echo "<td>" . h($book_title) . "</td>";
            echo "<td>" . h($book_publisher) . "</td>";
            echo "<td>" . h($from_school) . "</td>";
            echo "<td>" . h($to_school) . "</td>";
            echo "</tr>";
        }

    }
    

    try {
        $db = new db_connect();
        $db->connect();

        $sql = "SELECT * FROM school";
        $stmt = $db->pdo->prepare($sql);
        $stmt->execute();
        $selected_schools = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($selected_school_id != null) {

            $allowed_status = [5,6,8,9];    // 取得する書籍の配送状態（配送中か配送待ち）
            $waiting_deliverd = [5,8];
            $in_delivery = [6,9];
            $inClause = substr(str_repeat(',?', count($allowed_status)),1);

            // 書籍状態が$allowed_status↑に含まれるやつを取得
            $get_delList = "SELECT 
                                bs.book_id,
                                bs.status_id,
                                bi.title,
                                bi.isbn,
                                bi.publisher,
                                CASE
                                 WHEN bs.status_id IN (6, 9) THEN dt_school.school_id
                                 ELSE bs.position
                                END AS departure_school_id,

                                CASE
                                 WHEN bs.status_id IN (6, 9) THEN dt_school.school_name
                                 ELSE ps_school.school_name
                                END AS departure_school_name,

                                CASE
                                 WHEN bs.status_id IN (6, 9) THEN ds_school.school_id
                                 WHEN bs.status_id = 5 THEN rs_school.school_id
                                 WHEN bs.status_id = 8 THEN bs_school.school_id
                                END AS destination_school_id,

                                CASE
                                 WHEN bs.status_id IN (6, 9) THEN ds_school.school_name
                                 WHEN bs.status_id = 5 THEN rs_school.school_name
                                 WHEN bs.status_id = 8 THEN bs_school.school_name
                                END AS destination_school_name                           
                            FROM book_stack AS bs 
                            LEFT OUTER JOIN book_info AS bi 
                            ON bs.isbn = bi.isbn 
                            LEFT OUTER JOIN reservation AS rs 
                            ON bs.book_id = rs.book_id AND rs.status_id = 1 
                            LEFT OUTER JOIN student AS st 
                            ON rs.student_id = st.student_id 
                            LEFT OUTER JOIN delivery AS dl 
                            ON bs.book_id = dl.book_id AND dl.delivery_status <> 3 
                            LEFT OUTER JOIN school AS ps_school 
                            ON bs.position = ps_school.school_id 
                            LEFT OUTER JOIN school AS bs_school 
                            ON bs.school_id = bs_school.school_id 
                            LEFT OUTER JOIN school AS dt_school 
                            ON dl.from_school_id = dt_school.school_id 
                            LEFT OUTER JOIN school AS ds_school 
                            ON dl.to_school_id = ds_school.school_id 
                            LEFT OUTER JOIN school AS rs_school 
                            ON st.school_id = rs_school.school_id 
                            WHERE bs.status_id IN ($inClause) 
                            ";
            $stmt_deList = $db->pdo->prepare($get_delList);
            $stmt_deList->execute($allowed_status);
            $display_book_list = $stmt_deList->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($display_book_list)) {

                foreach ($display_book_list as $row) {
                    //　送り先の学校IDが現在地として選ばれた学校IDと同じなら、現在地の学校に搬入されるリストとなる
                    if (intval($row['destination_school_id']) == $selected_school_id && in_array(intval($row['status_id']), $in_delivery)) {
                        $carry_in_list [] = $row;
                    }
                    
                    // 送り元の学校IDが現在地として選ばれた学校IDと同じなら、現在地の学校から搬出されるリストとなる
                    if (intval($row['departure_school_id']) == $selected_school_id && in_array(intval($row['status_id']), $waiting_deliverd)) {
                        $carry_out_list [] = $row;
                    }
                }                
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

<script>
    function confirmLogout() {
        if(window.confirm('本当にログアウトしますか？')) {
            document.link_logoutFORM.submit();
        }
    }
</script>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>配送管理画面</title>
    <link rel="stylesheet" href="../css/librarian_myPage.css">
</head>
<body>
    <!-- ログアウトボタンを押したときのCSFSトークン発行 -->
        <form method="POST" action = "../php/logout.php" name = "link_logoutFORM">
            <?php
                set_csrf_token($csrf_token);
            ?>
            <input type="hidden" name = "page_id" value= "2">
        </form>

    <header class="main-header">
        <div class="header-logo">
            <a href="deliverer_book_management.php">配送管理画面(<?php echo h($deliverer_full_name); ?>さん)</a>
        </div>
        <nav class="header-nav">
            <ul>
                <li><a href="#" onclick="alert('余裕があったら、マイページの使い方やヘルプ等を説明するページを作ってもいいかも？'); return false;">はじめての方へ</a></li>
                <li><a href="#" onclick = "confirmLogout(); return false;">ログアウト</a></li>
            </ul>
        </nav>
    </header>

    <!-- 学校を選択させる -->
    <form action="deliverer_book_management.php" method="POST">
        <label for="school">現在地の学校:</label>
        <select name = "selected_school_id" onchange="this.form.submit()">
            <option value = "">現在地を選択してください</option>
            <?php foreach ($selected_schools as $school): ?>
                <?php if ($school['school_id'] == 0) continue; ?>
                <option value = "<?php echo h($school['school_id']); ?>"
                    <?php if ($selected_school_id == $school['school_id']) echo 'selected'; ?>>
                    <?php echo h($school['school_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <div class="tabs">
        <button onclick="showTab('in')">① 搬入リスト</button>
        <button onclick="showTab('out')">② 搬出リスト</button>
    </div>

    <div id="carry-in" class="tab-content" style="display:none;">
        <form action="deliverer_change_confirm.php" method="post">
            <p>学校へ搬入するリストです。チェックして確認画面へ進んでください。</p>
            <table>
                <?php table_data_display($carry_in_list, 1) ?>
            </table>
            <input type="hidden" name="next_status" value="15">
            <button type="submit">確認画面へ</button>
        </form>
    </div>

    <div id="carry-out" class="tab-content" style="display:none;">
        <form action="deliverer_change_confirm.php" method="post">
            <p>学校から搬出するリストです。チェックして確認画面へ進んでください。</p>
            <table>
                <?php table_data_display($carry_out_list, 2) ?>
            </table>
            <input type="hidden" name="next_status" value="13">
            <button type="submit">確認画面へ</button>
        </form>
    </div>

    <script>
    function showTab(tabName) {
        // 1. 一旦全部隠す
        document.getElementById('carry-in').style.display = 'none';
        document.getElementById('carry-out').style.display = 'none';
        
        // 2. 選ばれたやつだけ表示
        document.getElementById('carry-' + tabName).style.display = 'block';
    }
    </script>

    <div class="button-row">
        <button type="button" class="btn" onclick="location.href='../html/deliverer_myPage.php'">配送員マイページへ戻る</button>
    </div>
</body>
</html>