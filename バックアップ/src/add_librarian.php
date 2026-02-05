<?php
    require_once 'db_connect.php'; // パスは環境に合わせて調整してください
    session_start();

    $message = "";

    if (isset($_POST['add_librarians'])) {
        $db = new db_connect();
        
        try {
            $db->connect();
            $pdo = $db->pdo;

            $pdo->beginTransaction();

            // 司書テーブルへのINSERT文
            $sql = "INSERT INTO librarian (school_id, login_id, password, family_name, first_name) 
                    VALUES (:school_id, :login_id, :password, :family_name, :first_name)";
            
            $stmt = $pdo->prepare($sql);

            // 全員共通の初期パスワード
            $default_pass = 'password123'; 
            $hashed_password = password_hash($default_pass, PASSWORD_DEFAULT);

            // 3番（3小）から10番（10中）までループ
            for ($i = 3; $i <= 10; $i++) {
                
                // ログインID: lib_03, lib_04 ...
                $login_id = sprintf("lib_%02d", $i);
                
                // 名前: 管理者3, 管理者4 ...
                $first_name_val = "管理者" . $i;

                $stmt->bindValue(':school_id', $i, PDO::PARAM_INT);
                $stmt->bindValue(':login_id', $login_id, PDO::PARAM_STR);
                $stmt->bindValue(':password', $hashed_password, PDO::PARAM_STR);
                $stmt->bindValue(':family_name', '図書', PDO::PARAM_STR); // 苗字は固定
                $stmt->bindValue(':first_name', $first_name_val, PDO::PARAM_STR);
                
                $stmt->execute();
            }

            $pdo->commit();
            $message = "ID:3〜10の司書アカウントを作成しました。<br>（苗字：図書、名前：管理者[ID]）";

        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            if ($e->getCode() == 23000) {
                $message = "エラー：既にデータが存在するか、IDが重複しています。<br>" . $e->getMessage();
            } else {
                $message = "データベースエラー：" . $e->getMessage();
            }
        } catch (Exception $e) {
            $message = "エラー：" . $e->getMessage();
        } finally {
            $db->close();
        }
    }
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>司書アカウント追加</title>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        .message { 
            color: #d9534f; 
            background-color: #fdf7f7; 
            padding: 15px; 
            border-left: 5px solid #d9534f;
            margin-bottom: 20px; 
        }
        h1 { border-bottom: 2px solid #333; padding-bottom: 10px; }
        button { 
            padding: 12px 24px; 
            font-size: 16px; 
            cursor: pointer; 
            background-color: #3498db; 
            color: white; 
            border: none; 
            border-radius: 5px; 
        }
        button:hover { background-color: #2980b9; }
        .info-box {
            background-color: #f4f7f6;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    
    <h1>司書テーブルデータ追加ページ</h1>
    <h2>add_librarians.phpを実行</h2>

    <?php if ($message): ?>
        <div class="message"><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="info-box">
        <h3><span style="color: red;">注意</span></h3>
        <p>以下のボタンを押すと、<b>school_id 3〜10</b> の司書データが挿入されます。</p>
        <ul>
            <li>ログインID形式: <code>lib_03</code> 〜 <code>lib_10</code></li>
            <li>初期パスワード: <code>password123</code></li>
            <li>登録名: <code>図書 管理者[ID]</code></li>
        </ul>
    </div>

    <form method="post" onsubmit="return confirm('本当に司書アカウント(ID 3-10)を追加しますか？\n既にデータがある場合はエラーになります。');">
        <button type="submit" name="add_librarians">司書アカウントを追加</button>
    </form>

    <br>
    <a href="insert.php">挿入ページへ移動</a>
</body>
</html>