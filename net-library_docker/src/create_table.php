<?php
    require_once 'db_connect.php';
    $tables = ['delivery', 'reservation', 'lending', 'book', 'book_status', 'reservation_status', 'delivery_status', 'delivery_type', 'student', 'school', 'librarian'];

    if (isset($_POST['reset_table'])) {
        $db = new db_connect();
        $db->connect(); //データベースへ接続

        try {
            //テーブル構成を変更した場合を考慮して、外部キー制約を一時的に無効化
            $sql = "SET FOREIGN_KEY_CHECKS=0;";
            $stmt = $db->pdo->prepare($sql);
            $stmt->execute();

            //既存のテーブルを削除
            foreach ($tables as $table) {
                $sql = "DROP TABLE IF EXISTS $table;";
                $stmt = $db->pdo->prepare($sql);
                $stmt->execute();
            }

            //学校テーブル作成
            $sql = "CREATE TABLE IF NOT EXISTS school (
                school_id INT PRIMARY KEY,
                school_name VARCHAR(100),
                has_library BOOLEAN
            );";
            $stmt = $db->pdo->prepare($sql);
            $stmt->execute();


            //学生テーブル作成
            $sql = "CREATE TABLE IF NOT EXISTS student (
                student_id INT AUTO_INCREMENT PRIMARY KEY,
                school_id INT NOT NULL,
                grade INT NOT NULL,
                class VARCHAR(10) NOT NULL,
                number INT NOT NULL,
                family_name VARCHAR(50),
                first_name VARCHAR(50),
                password VARCHAR(100),
                FOREIGN KEY (school_id) REFERENCES school(school_id),
                UNIQUE (school_id, grade, class, number)
            );";
            $stmt = $db->pdo->prepare($sql);
            $stmt->execute();

            //書籍状態テーブル作成
            $sql = "CREATE TABLE IF NOT EXISTS book_status (
                status_id INT PRIMARY KEY,
                status_name VARCHAR(50)
            );";
            $stmt = $db->pdo->prepare($sql);
            $stmt->execute();

            //予約状態テーブルの作成
            $sql = "CREATE TABLE IF NOT EXISTS reservation_status (
                status_id INT PRIMARY KEY,
                status_name VARCHAR(50)
            );";
            $stmt = $db->pdo->prepare($sql);
            $stmt->execute();

            //配送状態テーブルの作成
            $sql = "CREATE TABLE IF NOT EXISTS delivery_status (
                status_id INT PRIMARY KEY,
                status_name VARCHAR(50)
            );";
            $stmt = $db->pdo->prepare($sql);
            $stmt->execute();

            //配送タイプテーブルの作成
            $sql = "CREATE TABLE IF NOT EXISTS delivery_type (
                type_id INT PRIMARY KEY,
                type_name VARCHAR(50)
            );";
            $stmt = $db->pdo->prepare($sql);
            $stmt->execute();

            //書籍テーブル作成
            $sql = "CREATE TABLE IF NOT EXISTS book (
                book_id VARCHAR(20) PRIMARY KEY,
                school_id INT,
                title VARCHAR(200),
                author_name VARCHAR(100),
                author_kana VARCHAR(100),
                publisher VARCHAR(100),
                publication_year DATE,
                status_id INT,
                position INT,
                FOREIGN KEY (school_id) REFERENCES school(school_id),
                FOREIGN KEY (status_id) REFERENCES book_status(status_id),
                FOREIGN KEY (position) REFERENCES school(school_id)
            );";
            $stmt = $db->pdo->prepare($sql);
            $stmt->execute();

            //予約テーブル作成
            $sql = "CREATE TABLE IF NOT EXISTS reservation (
                reservation_id INT AUTO_INCREMENT PRIMARY KEY,
                student_id INT,
                book_id VARCHAR(20),
                status_id INT,
                reservation_date DATETIME,
                FOREIGN KEY (student_id) REFERENCES student(student_id),
                FOREIGN KEY (book_id) REFERENCES book(book_id),
                FOREIGN KEY (status_id) REFERENCES reservation_status(status_id)
            );";
            $stmt = $db->pdo->prepare($sql);
            $stmt->execute();

            //貸出テーブル作成
            $sql = "CREATE TABLE IF NOT EXISTS lending (
                lending_id INT AUTO_INCREMENT PRIMARY KEY,
                student_id INT,
                book_id VARCHAR(20),
                lending_date DATETIME,
                return_date DATETIME,
                FOREIGN KEY (student_id) REFERENCES student(student_id),
                FOREIGN KEY (book_id) REFERENCES book(book_id)
            );";
            $stmt = $db->pdo->prepare($sql);
            $stmt->execute();

            //配送テーブル作成
            $sql = "CREATE TABLE IF NOT EXISTS delivery (
                delivery_id INT AUTO_INCREMENT PRIMARY KEY,
                from_school_id INT NOT NULL,
                to_school_id INT NOT NULL,
                delivery_type INT,
                delivery_status INT,
                book_id VARCHAR(20),
                delivery_date DATETIME,
                arrival_date DATETIME,
                FOREIGN KEY (from_school_id) REFERENCES school(school_id),
                FOREIGN KEY (to_school_id) REFERENCES school(school_id),
                FOREIGN KEY (book_id) REFERENCES book(book_id),
                FOREIGN KEY (delivery_type) REFERENCES delivery_type(type_id),
                FOREIGN KEY (delivery_status) REFERENCES delivery_status(status_id)
            );";
            $stmt = $db->pdo->prepare($sql);
            $stmt->execute();

            //司書テーブル作成
            $sql = "CREATE TABLE IF NOT EXISTS librarian (
                librarian_id INT AUTO_INCREMENT PRIMARY KEY,
                school_id INT NOT NULL,
                login_id VARCHAR(20) NOT NULL,
                password VARCHAR(100),
                family_name VARCHAR(50),
                first_name VARCHAR(50),
                FOREIGN KEY (school_id) REFERENCES school(school_id),
                UNIQUE (school_id, login_id)
            );";
            $stmt = $db->pdo->prepare($sql);
            $stmt->execute();

            //外部キー制約を再度有効化
            $sql = "SET FOREIGN_KEY_CHECKS=1;";
            $stmt = $db->pdo->prepare($sql);
            $stmt->execute();

            $error_message = "テーブルの再作成が完了しました。";
            echo "<script>alert('" . htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8') . "');</script>";


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
    <title>テーブル作成</title>
</head>
<body>
    
    <h1>テーブル作成ページ</h1>
    <h2>create_table.phpを実行</h2>
    <p>注意：以下のボタンを押すと、今までのテーブルが<b>削除</b>され、新しいテーブルが作成される<br></p>
    <form method="post" onsubmit="return confirm('本当にテーブルを再作成しますか？ 今までのデータはすべて失われます。チームメンバーに確認取った？ダイジョブか？？');">
        <button type = "submit" name = "reset_table">テーブルを再度作成</button>
    </form>
    <a href="insert.php">挿入ページへ移動</a>
</body>
</html>