<?php
    require_once '../db_connect.php';
    session_start();
    $db = new db_connect();
    $db->connect();

    $error_message = $_SESSION['error'] ?? '';
    if (isset($_SESSION['error'])) {
        echo "<script>alert('" . htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8') . "');</script>";
        unset($_SESSION['error']);
    }

    try {
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
    <title>貸出予約ページ</title>
    <link rel="stylesheet" href="../css/reservation.css">
</head>
<body class="reservation-body">
    <header class="main-header">
        <div class="header-logo">
            <a href="librarian_myPage.php">貸出予約ページ</a>
        </div>
    </header>

    <div class="reservation-container">
        <h2>貸出予約フォーム</h2>
        
        <form action="../php/reservation_process.php" method="POST" class="reservation-form">
            <?php 
                $toke_byte = random_bytes(16);
                $csrf_token = bin2hex($toke_byte);
                $_SESSION['csrf_token'] = $csrf_token;
            ?>
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

            <div class="form-group">
                <label for="school">学校</label>
                <select name="school" id="school" required>
                    <option value="">選択してください</option>
                    <?php foreach ($schools as $school): ?>
                        <?php if ($school['school_id'] == 0) continue; ?>
                        <option value="<?php echo htmlspecialchars($school['school_id']); ?>">
                            <?php echo htmlspecialchars($school['school_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group inline-group">
                <div class="field">
                    <select name="grade" id="grade" required>
                        <option value=""></option>
                        <?php for($g=1; $g<=9; $g++): ?>
                            <option value="<?php echo $g; ?>"><?php echo $g; ?></option>
                        <?php endfor; ?>
                    </select>
                    <label for="grade">年</label>
                </div>

                <div class="field">
                    <input type="text" id="class" name="class" required>
                    <label for="class">組</label>
                </div>

                <div class="field">
                    <select name="number" id="number" required>
                        <option value=""></option>
                        <?php for ($i = 1; $i <= 50; $i++): ?>
                            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                    <label for="number">番</label>
                </div>
            </div>

            <div class="form-group">
                <label for="book_id">書籍ID</label>
                <input type="text" id="book_id" name="book_id" required placeholder="例: 12345">
            </div>

            <div class="form-actions">
                <button type="button" class="btn-cancel" onclick="history.back()">キャンセル</button>
                <button type="submit" class="btn-submit">予約</button>
            </div>
        </form>
    </div>
</body>
</html>