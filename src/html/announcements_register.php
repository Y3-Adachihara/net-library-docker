<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>お知らせ登録</title>
    <link rel="stylesheet" href="../css/検索画面.css"> </head>
<body>
    <div class="container">
        <h1>お知らせ新規登録</h1>
        <form action="../php/announcements_insert.php" method="post">
            <div class="form-group">
                <label>カテゴリ：</label>
                <select name="announcements_category" required>
                    <option value="重要">重要</option>
                    <option value="イベント">イベント</option>
                    <option value="新着">新着</option>
                    <option value="その他">その他</option>
                </select>
            </div>

            <div class="form-group">
                <label>タイトル：</label>
                <input type="text" name="announcements_title" required style="width: 100%;">
            </div>

            <div class="form-group">
                <label>本文：</label>
                <textarea name="announcements_content" rows="10" required style="width: 100%;"></textarea>
            </div>

            <div class="button-row">
                <button type="button" class="btn" onclick="location.href='librarian_myPage.php'">戻る</button>
                <button type="submit" class="btn btn-blue">登録する</button>
            </div>
        </form>
    </div>
</body>
</html>