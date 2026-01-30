<?php
    require_once '../db_connect.php';
    session_start();
    
    if (!isset($_SESSION['deliverer_id'])) {
        // 配送員としてログインしていない場合、ログインページへリダイレクト
        $_SESSION['message'] = "配送員としてログインしてください。";
        header("Location: deliverer_login.php");
        exit();
    }

    //　ここからは、配送員としてログインしていないと実行されない
    $deliverer_family_name = $_SESSION['deliverer_family_name'] ?? '';
    $deliverer_first_name = $_SESSION['deliverer_first_name'] ?? '';
    $deliverer_full_name = $deliverer_family_name . " " . $deliverer_first_name ?? '';

    $unload_list = [];  // 荷下ろし（配送してきた本）
    $pickup_list = []; // 集荷（これから配送する本）
    $selected_school_id = $_POST['selected_school_id'] ?? null;

    $selected_schools = null;    // 配送で用いる学校リスト

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
    

    try {
        $db = new db_connect();
        $db->connect();

        $sql = "SELECT * FROM school";
        $stmt = $db->pdo->prepare($sql);
        $stmt->execute();
        $selected_schools = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($selected_school_id != null) {

            $get_school_id = intval($selected_school_id);
            $allowed_status = [5,6,8,9];    // 取得する書籍の配送状態（配送中か配送待ち）
            $inClause = substr(str_repeat(',?', count($allowed_status)),1);

            // 選択されている学校で、書籍状態が$allowed_statusに含まれるやつを取得
            $get_delList = "SELECT * FROM book_stack AS bs 
                            LEFT OUTER JOIN book_info AS bi 
                            ON bs.isbn = bi.isbn 
                            LEFT OUTER JOIN reservation AS r 
                            ON bs.book_id = r.book_id 
                            LEFT OUTER JOIN student AS st 
                            ON r.student_id = st.student_id 
                            WHERE bs.school_id = ? 
                            AND bs.status_od IN ($inClause)
                            "
            $stmt_deList = $db->pdo->prepare($get_delList);
            $stmt_deList->execute([$selected_school_id],$allowed_status);
            $display_school_list = $stmt_deList->fetchAll(PDO::FETCH_ASSOC);
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
    <label for="school">学校:</label>
    <select name = "selected_school_id" onchange="this.form.submit()">
        <option value = "">現在地を選択してください</option>
        <?php foreach ($selected_schools as $school): ?>
            <?php if ($school['school_id'] == 0) continue; ?>
            <option value = "<?php echo htmlspecialchars($school['school_id']); ?>">
                <?php echo htmlspecialchars($school['school_name']); ?>
            </option>
        <?php endforeach; ?>
    </select>

    <div class="tabs">
        <button onclick="showTab('in')">① 搬入リスト</button>
        <button onclick="showTab('out')">② 搬出リスト</button>
    </div>

    <div id="carry-in" class="tab-content" style="display:none;">
        <form action="deliverer_change_confirm.php" method="post">
            <input type="hidden" name="">
            <p>学校へ搬入するリストです。チェックして確認画面へ進んでください。</p>
            <table>
                <?php //table_data_display($local_reservations, 1) ?>
            </table>
            <input type="hidden" name="next_status" value="4">
            <button type="submit">確認画面へ</button>
        </form>
    </div>

    <div id="carry-out" class="tab-content" style="display:none;">
        <form action="librarian_book_confirm.php" method="post">
            <p>学校から搬出するリストです。チェックして確認画面へ進んでください。</p>
            <table>
                <?php //table_data_display($deliver_reservations, 2) ?>
            </table>
            <input type="hidden" name="next_status" value="5">
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