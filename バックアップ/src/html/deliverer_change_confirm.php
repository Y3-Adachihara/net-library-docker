<?php
    session_start();

    if (!isset($_SESSION['deliverer_id'])) {
        // 配送員としてログインしていない場合、ログインページへリダイレクト
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
        echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') . '">';
    }
    
    //CSRFトークン(書籍状態変更の前に)
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = csrf_token_generate();
    }
    $csrf_token = $_SESSION['csrf_token'];


    // 表示データの受け取り
    $list_data = [];
    $action_type = ''; // 'in' or 'out'

    if (!empty($_POST['carry_in_list'])) {
        $list_data = $_POST['carry_in_list'];
        $action_type = 'in'; // 搬入
    } elseif (!empty($_POST['carry_out_list'])) {
        $list_data = $_POST['carry_out_list'];
        $action_type = 'out'; // 搬出
    } else {
        // データがない場合は戻す
        $_SESSION['book_manageConfirm_message'] = "書籍リストを選択してください。";
        header("Location: deliverer_book_management.php");
        exit();
    }

    // HTMLエスケープ関数
    function h($str) {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>配送処理確認</title>
    </head>
<body>
    <h2>以下の内容で処理を確定しますか？</h2>

    <form action="../php/change_delBook_status.php" method="POST">
        
        <input type="hidden" name="csrf_token" value="<?php echo h($_POST['csrf_token'] ?? ''); ?>">

        <table>
            <tr>
                <th>書籍ID</th>
                <th>タイトル</th>
                <th>処理内容</th>
            </tr>
            
            <?php 
            foreach ($list_data as $i => $packed_value): 
                // 分解
                // 順序: ID | ISBN | タイトル | 出版社 | 送り元 | 宛先 | 現在ステータス
                $data = explode('|', $packed_value);
                
                $book_id     = $data[0];
                $title       = $data[2];
                $current_status = intval($data[6]);

                $from_id = $data[7] ?? null;
                $to_id = $data[8] ?? null;
                
                $next_status = 0;
                $status_text = "";

                if ($action_type === 'in') {
                    $next_status = 10; 
                    $status_text = "学校へ搬入（保管中へ）";

                } elseif ($action_type === 'out') {
                    // 搬出（集荷）の場合
                    if ($current_status == 5) {
                        // 予約(5) → 予約配送中(6)
                        $next_status = 6;
                        $status_text = "集荷（予約配送中へ）";
                    } elseif ($current_status == 8) {
                        // 返却(8) → 返却配送中(9)
                        $next_status = 9;
                        $status_text = "集荷（返却配送中へ）";
                    }
                }
            ?>
                <tr>
                    <td><?php echo h($book_id); ?></td>
                    <td><?php echo h($title); ?></td>
                    <td><?php echo h($status_text); ?></td>
                </tr>

                
                <input type="hidden" name="update_items[<?php echo $i; ?>][book_id]" value="<?php echo h($book_id); ?>">
                <input type="hidden" name="update_items[<?php echo $i; ?>][next_status]" value="<?php echo h($next_status); ?>">

                <input type="hidden" name="update_items[<?php echo $i; ?>][from_id]" value="<?php echo h($from_id); ?>">
                <input type="hidden" name="update_items[<?php echo $i; ?>][to_id]" value="<?php echo h($to_id); ?>">
                
                <?php endforeach; ?>
        </table>

        <?php
            set_csrf_token($csrf_token);
        ?>

        <div class="button-row">
            <button type="button" onclick="history.back()">戻る</button>
            <button type="submit">確定する</button>
        </div>
    </form>
</body>
</html>