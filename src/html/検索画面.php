<?php
    require_once '../db_connect.php'; // DB接続ファイル
	session_start();

	if (isset($_SESSION['search_result_message'])) {
		$message = $_SESSION['search_result_message'];
		echo "<script>alert('" . htmlspecialchars($message, ENT_QUOTES, 'UTF-8')."');</script>";
		unset($_SESSION['search_result_message']);
	}

    if (!isset($_SESSION['librarian_id'])) {
        // 司書としてログインしていない場合、ログインページへリダイレクト
        $_SESSION['message'] = "司書としてログインしてください。";
        header("Location: librarian_login.php");
        exit();
    }
    $librarian_school_id = isset($_SESSION['librarian_school_id']) ? $_SESSION['librarian_school_id'] : null;

    $librarian_school_name = '';
    $librarian_fullname = '';

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

    try {
        $db = new db_connect();
        $db->connect();

        $sql = "SELECT librarian.family_name, librarian.first_name, school.school_name FROM librarian LEFT JOIN school ON librarian.school_id = school.school_id WHERE librarian_id = ?";
        $stmt = $db->pdo->prepare($sql);
        $stmt->execute([$_SESSION['librarian_id']]);
        $librarian = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($librarian) {
            $librarian_fullname = $librarian['family_name'] . ' ' . $librarian['first_name'];
            $librarian_school_name = $librarian['school_name'];
        }

        $sql = "SELECT * FROM school";
        $stmt = $db->pdo->prepare($sql);
        $stmt->execute();
        $schools = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($schools) {
            foreach ($schools as $school) {
                if ($school['school_id'] == $librarian_school_id) {
                    $librarian_school_name = $school['school_name'];
                    break;
                }
            }
        }

        $sql = "SELECT * FROM book_status";
        $stmt = $db->pdo->prepare($sql);
        $stmt->execute();
        $status_list = $stmt->fetchAll(PDO::FETCH_ASSOC); // status_id と status_name の連想配列を取得
    } catch (PDOException $e) {
        echo "データベースエラー: " . h($e->getMessage());
        exit;
    } catch (Exception $e) {
        echo "エラー: " . h($e->getMessage());
        exit;
    } finally {
        $db->close(); // DB接続解除   
    }
?>


<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>検索画面</title>
    <link rel="stylesheet" href="../css/検索画面.css">
	<style>
        /* ここに追加 */
        input::placeholder {
            color: #ff9999;  /* ちょっと薄めのグレー */
            font-size: 0.9em; /* 文字も少し小さくすると「例」っぽさが出ます */
        }
    </style>
</head>
<body>

<!-- ログアウトボタンを押したときのCSRFトークン発行 -->
<form method="POST" action = "../php/logout.php" id = "logout_form">
        <?php 
            set_csrf_token($csrf_token);
        ?>
        <input type="hidden" name = "page_id" value= "0">
    </form>


<header>
    <a href="#"><?php echo htmlspecialchars($librarian_fullname); ?> さん(<?php echo htmlspecialchars($librarian_school_name); ?>)の検索画面</a>
    <button class="logout-btn" onclick="confirmLogout()">ログアウト</button>
</header>


<div class="container">
    <h1>司書検索画面</h1>

    <form action="検索結果.php" method="get" id="searchForm">
        <div class="search-section">
            <div class="title-or-id-group">
                <div class="label-box">
                    <span>タイトル</span>
                    <span class="or-text">or</span>
                    <span>書籍ID</span>
                </div>
                <div class="input-stack">
                    <input type="text" name="search-title" placeholder="タイトルを入力">
                    <input type="text" name="search-id" placeholder="書籍IDを入力">
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
            </div>

            <div class="form-group">
                <label>ジャンル(目)：</label>
                <input type="text" name="genre-me" id="me" placeholder="例: 3 (小説) 数字のみ入力">
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
            </div>
        </div>

        <div class="button-row">
            <button type="button" class="btn" onclick="location.href='librarian_myPage.php'">戻る</button>
            <button type="reset" class="btn btn-blue">検索の取消</button>
            <button type="submit" class="btn btn-blue">検索</button>
        </div>
    </form>
</div>

<script>
    function confirmLogout() {
            if (confirm("ログアウトしますか？")) {
                document.getElementById('logout_form').submit();
            }
        }


    document.getElementById('searchForm').addEventListener('submit', function(e) {
        // .trim() をつけることで、スペースのみの入力を「空文字」として扱います
        const rui = document.getElementById('rui').value.trim();
        const mou = document.getElementById('mou').value.trim();
        const me  = document.getElementById('me').value.trim();

        const title = document.querySelector('input[name="search-title"]').value.trim();
        const id = document.querySelector('input[name="search-id"]').value.trim();
        const publisher = document.querySelector('input[name="search-publisher"]').value.trim();
        const author = document.querySelector('input[name="search-author"]').value.trim();

        // 【ルール1】「類」が入力されているのに、「網」か「目」が空欄の場合
        // trim()しているので、スペースが入っていてもここは空文字("")扱いになり、if文の中に入りません
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
</html>