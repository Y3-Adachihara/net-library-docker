<?php
    session_start();
    require_once '../db_connect.php';

    header("Cache-Control:no-cache,no-store,must-revalidate,max-age=0,post-check=0,pre-check=0");
    header("Pragma:no-cache");

    if (!isset($_SESSION['deliverer_id'])) {
        // 司書としてログインしていない場合、ログインページへリダイレクト
        $_SESSION['message'] = "配送員としてログインしてください。";
        header("Location: deliverer_login.php");
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
        //ここでトークンを隠し属性として送るためのhtmlコードを記述
        echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') . '">';
    }

    //　ここからは、司書としてログインしていないと実行されない
    $deliverer_family_name = $_SESSION['deliverer_family_name'] ?? '';
    $deliverer_first_name = $_SESSION['deliverer_first_name'] ?? '';
    $deliverer_full_name = $deliverer_family_name . " " . $deliverer_first_name ?? '';

    /* 予約参照画面のメッセージ
    $error_message = $_SESSION['res_refer_error'] ?? null;
    if (isset($_SESSION['res_refer_error'])) {
        echo "<script>alert('" . htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8') . "');</script>";
        unset($_SESSION['res_refer_error']);
    }*/

    // HTMLエスケープ関数
    function h($str) {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>配送員用マイページ</title>
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
            <a href="deliverer_myPage.php">配送員用マイページへようこそ、<?php echo h($deliverer_full_name); ?>さん！   </a>
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
            <input type="hidden" name = "page_id" value= "2">
        </form>

        <form method="GET" class="librarian-menu-form">
            <button type="submit" formaction="../html/検索画面.html" class="add-book-button">書籍検索</button>
            <button type="submit" formaction="../html/貸出返却.php" class="manage-users-button">貸出・返却</button>
            <button type="submit" formaction="../html/librarian_reservation_reference.php" class="manage-users-button">予約状況参照</button>
            <button type="submit" formaction="../html/書籍登録.html" class="add-book-button">新規書籍登録</button>
            <button type="submit" formaction="../html/librarian_bookManagement.php" class="add-book-button">予約された本の管理画面</button>
        </form>

        <div class ="info-table-container">
            <h2 >配送員（<?php echo h($deliverer_full_name); ?>さん）の配送履歴</h2>

            <div class = "scroll-wrapper">
                <table class="info-table">
                    <thead>
                        <tr>
                            <th>書籍ID</th> <!-- 貸出IDから変更 -->
                            <th>タイトル</th>
                            <th>出版社</th>
                            <th>所属</th>
                            <th>貸出者名</th>
                            <th>貸出日</th>
                            <th>返却日</th>
                            <th>貸出状態</th>   <!-- 貸出した学生の状態を記述するように変更 -->
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                            //table_data_display($fetchAll_record);
                        ?>
                    </tbody>

                </table>
            </div>
        </div>

    </body>
</html>