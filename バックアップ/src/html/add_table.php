<?php
    require_once 'db_connect.php'; 
    session_start();

    $message = "";

    if (isset($_POST['add_librarians'])) {
        $db = new db_connect();
        
        try {
            $db->connect();
            $pdo = $db->pdo;

            $pdo->beginTransaction();

            // SQL文：苗字(last_name)と名前(first_name)を追加
            // ※カラム名が `sei` `mei` などの場合は書き換えてください
            $sql = "INSERT INTO librarians (school_id, login_id, password, last_name, first_name) 
                    VALUES (:school_id, :login_id, :password, :last_name, :first_name)";
            
            $stmt = $pdo->prepare($sql);

            $default_pass = 'password123'; 
            $hashed_password = password_hash($default_pass, PASSWORD_DEFAULT);

            // テスト用の苗字リスト（ID 3～10に対応）
            $last_names_list = [
                3 => '佐藤',
                4 => '鈴木',
                5 => '高橋',
                6 => '田中',
                7 => '伊藤',
                8 => '渡辺',
                9 => '山本',
                10 => '中村'
            ];

            // 3番から10番までループ
            for ($i = 3; $i <= 10; $i++) {
                
                $login_id = sprintf("lib_%02d", $i);
                
                // リストから苗字を取得（なければ「テスト」）、名前は全員「司書」にする
                $lname = $last_names_list[$i] ?? 'テスト';
                $fname = '司書';

                $stmt->bindValue(':school_id', $i, PDO::PARAM_INT);
                $stmt->bindValue(':login_id', $login_id, PDO::PARAM_STR);
                $stmt->bindValue(':password', $hashed_password, PDO::PARAM_STR);
                
                // 追加したパラメータをバインド
                $stmt->bindValue(':last_name', $lname, PDO::PARAM_STR);
                $stmt->bindValue(':first_name', $fname, PDO::PARAM_STR);
                
                $stmt->execute();
            }

            $pdo->commit();
            $message = "ID:3〜10の司書アカウント（氏名付き）を作成しました。<br>初期パスワードは 'password123' です。";

        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            if ($e->getCode() == 23000) {
                $message = "エラー：既にデータが存在するか、重複しています。<br>" . $e->getMessage();
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
    <title>司書アカウント一括追加</title>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        .message { color: red; font-weight: bold; margin-bottom: 20px; }
        button { padding: 10px 20px; font-size: 16px; cursor: pointer; }
    </style>
</head>
<body>
    
    <h1>司書アカウント追加ページ</h1>
    <h2>add_librarians.phpを実行</h2>

    <?php if ($message): ?>
        <p class="message"><?php echo $message; ?></p>
    <?php endif; ?>

    <p>
        注意：以下のボタンを押すと、school_id 3〜10 に紐づく司書アカウントが作成されます。<br>
        ログインID: <code>lib_03</code> 〜 <code>lib_10</code><br>
        名前: 佐藤 司書、鈴木 司書 など
    </p>

    <form method="post" onsubmit="return confirm('本当に司書アカウント(ID 3-10)を追加しますか？');">
        <button type="submit" name="add_librarians">司書アカウントを追加</button>
    </form>

    <br>
    <a href="../index.php">トップページへ戻る</a>
</body>
</html>