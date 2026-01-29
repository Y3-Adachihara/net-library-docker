<?php
    require_once 'db_connect.php';

    if (isset($_POST['add_table'])) {

        try {
            $db = new db_connect();
            $db->connect(); //データベースへ接続

            $sql = "CREATE TABLE IF NOT EXISTS lending_deny (
                deny_id INT AUTO_INCREMENT PRIMARY KEY,
                pass_num VARCHAR(4),
                book_id VARCHAR(20),
                start_date DATETIME,
                end_date DATETIME,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (book_id) REFERENCES book_stack(book_id)
            );";
            $stmt = $db->pdo->prepare($sql);
            $stmt->execute();

        } catch (PDOException $e) {
            echo "データベースエラー：" . $e->getMessage(); //デバッグ用。あとで消す！
            throw new PDOException($e->getMessage(), (int)$e->getCode());
        } catch (Exception $e) {
            echo "エラー：" . $e->getMessage(); //デバッグ用。あとで消す！
            throw new Exception($e->getMessage(), (int)$e->getCode()); 
        } finally {
            $db->close(); //データベース接続を閉じる
        }
    }
?>

<html>
<head>
    <meta charset="UTF-8">
    <title>テーブル追加</title>
</head>
<body>
    
    <h1>テーブル追加ページ</h1>
    <h2>add_table.phpを実行</h2>
    <p>注意：以下のボタンを押すと、貸出拒否テーブルがテーブルが作成される<br></p>
    <form method="post" onsubmit="return confirm('本当にテーブルを再作成しますか？ 既にテーブルがある場合はエラーになるます。');">
        <button type = "submit" name = "add_table">テーブルを追加</button>
    </form>
    <a href="insert.php">挿入ページへ移動</a>
</body>
</html>