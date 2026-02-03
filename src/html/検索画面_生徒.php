<?php
	session_start();
    require_once('../db_connect.php');


	if (isset($_SESSION['search_result_message'])) {
		$message = $_SESSION['search_result_message'];
		echo "<script>alert('" . htmlspecialchars($message, ENT_QUOTES, 'UTF-8')."');</script>";
		unset($_SESSION['search_result_message']);
	}

    $student_school_id = $_SESSION['student_school_id'];

    try {
        $db = new db_connect();
        $db->connect();

        $sql = "SELECT * FROM school";
        $stmt = $db->pdo->prepare($sql);
        $stmt->execute();

        $schools = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        echo "データベースエラー：" . $e->getMessage(); //デバッグ用。あとで消す！
        exit;
    } catch (Exception $e) {
        echo "エラー：" . $e->getMessage(); //デバッグ用。あとで消す！
        exit;
    } finally {
        $db->close();
    }
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>検索画面</title>
    <link rel = "stylesheet" href="../css/検索画面.css">
	<style>
        /* ここに追加 */
        input::placeholder {
            color: #ff9999;  /* ちょっと薄めのグレー */
            font-size: 0.9em; /* 文字も少し小さくすると「例」っぽさが出ます */
        }
    </style>
</head>
<body>

</html>
<div class="container">
    <h1>生徒検索画面</h1>

    <form action="検索結果_生徒.php" method="get">
        <div class="search-section">
            <div class="title-or-id-group">
                <div class="label-box">
                    <span>タイトル</span>
                    <span class="or-text">or</span>
                    <span>識別番号</span>
                </div>
                <div class="input-stack">
                    <input type="text" name="search-title" placeholder="タイトルを入力">
                    <input type="text" name="search-id" placeholder="識別番号を入力">
                </div>
            </div>
        </div>

        <div class="search-section detailed-section">
            <div class="form-group">
                <label>ジャンル(類)：</label>
                <input type="text" name="genre-rui" id="rui" placeholder="例: 9 (文学) 数字のみ入力">
            </div>

            <div class="form-group">
            <label>ジャンル(網)：</label>
            <input type="text" name="genre-mou" id="mou" placeholder="例: 1 (日本) 数字のみ入力">
            <!--
            <select name="genre-mou">
                <option value="">選択してください</option>
            </select>
            -->
        </div>

        <div class="form-group">
            <label>ジャンル(目)：</label>
            <input type="text" name="genre-me" id="me" placeholder="例: 3 (小説) 数字のみ入力">
            <!--
            <select name="genre-me">
                <option value="">選択してください</option>
            </select>
        -->
        </div>

            <div class="form-group">
                <label>出版社：</label>
                <input type="text" name="search-publisher">
            </div>
            <div class="form-group">
                <label>著者名：</label>
                <input type="text" name="search-author">
            </div>
            <div class="form-group">
                <label>学校：</label>
                <select name="school-filter">
                    <option value="">指定しない</option>
                    <?php foreach ($schools as $school): ?>
                        <?php if ($school['school_id'] == 0) continue; ?>
                        <option value="<?php echo htmlspecialchars($school['school_id']); ?>">
                            <?php echo htmlspecialchars($school['school_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="hidden" name="student-school-id" value="<?php echo htmlspecialchars($student_school_id); ?>">
            </div>
        </div>

        <div class="button-row">
            <button type="button" class="btn" onclick="location.href='stu_myPage.php'">戻る</button>
            <button type="reset" class="btn btn-blue">検索の取消</button>
            <button type="submit" class="btn btn-blue">検索</button>
        </div>
    </form>
</div>

<script>
    document.querySelector('form').addEventListener('submit', function(e) {
        // .trim() をつけることで、スペースのみの入力を「空文字」として扱います
        // ※上でid="rui"などを追加していないとエラーになるので注意！
        const rui = document.getElementById('rui').value.trim();
        const mou = document.getElementById('mou').value.trim();
        const me  = document.getElementById('me').value.trim();

        const title = document.querySelector('input[name="search-title"]').value.trim();
        const id = document.querySelector('input[name="search-id"]').value.trim();
        const publisher = document.querySelector('input[name="search-publisher"]').value.trim();
        const author = document.querySelector('input[name="search-author"]').value.trim();

        // 【ルール1】「類」が入力されているのに、「網」か「目」が空欄の場合
        if (rui !== "") {
            if (mou === "" || me === "") {
                e.preventDefault();
                alert("ジャンル検索をする場合は、(類)・(網)・(目) すべてを入力してください。");
                return;
            }
        }

        /* 【ルール2】すべての項目が空っぽの場合
        if (title === "" && id === "" && publisher === "" && author === "" && rui === "") {
            e.preventDefault();
            alert("検索条件を少なくとも1つ入力してください。");
        }
            */
    });
</script>

</body>