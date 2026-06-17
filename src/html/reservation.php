<?php
    require_once '../db_connect.php';
    session_start();

    $error_message = $_SESSION['error'] ?? '';
    if (isset($_SESSION['error'])) {
        echo "<script>alert('" . htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8') . "');</script>";
        unset($_SESSION['error']);
    }

    $error_message = null;
    try {
        $db = new db_connect();
        $db->connect();

        $sql = "SELECT * FROM school";
        $stmt = $db->pdo->prepare($sql);
        $stmt->execute();

        $schools = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        error_log("DBエラー：" . $e->getMessage());
        $error_message = "データベース通信エラーが発生しました。しばらく経ってからやり直してください。";
    } catch (PDOexception $e) {
        error_log("予期しないエラー: " . $e->getMessage());
        $error_message = "予期せぬエラーが発生しました。システム管理者にお問い合わせください。";
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
<? if ($error_message !== null): ?>
            <div style="color: red; padding: 20px; border: 1px solid red; background-color: #fee; text-align: center;">
                <h3>現在システムをご利用いただけません</h3>
                <p><?php echo h($error_message); ?></p>
            </div>
<?php else: ?>
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
                if (empty($_SESSION['csrf_token'])) {
                    $toke_byte = random_bytes(16);
                    $_SESSION['csrf_token'] = bin2hex($toke_byte);
                }
            ?>
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

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
<?php endif; ?>
</html>