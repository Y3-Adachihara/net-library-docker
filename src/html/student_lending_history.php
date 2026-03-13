<?php
session_start();
// ★ここが重要：共通の接続ファイルを読み込みます
require_once '../db_connect.php';

// ログイン確認
if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit();
}

// 生徒名の取得（stu_myPage.phpと同じ方法で取得します）
$family_name = $_SESSION['student_family_name'] ?? '';
$first_name = $_SESSION['student_first_name'] ?? '';
$student_name = $family_name . " " . $first_name;
$student_id = $_SESSION['student_id'];

$results = [];
$error_message = null;

try {
    $db = new db_connect();
    $db->connect();

	$sql = "SELECT * FROM lending 
        JOIN book_stack ON lending.book_id = book_stack.book_id 
        JOIN book_info ON book_stack.isbn = book_info.isbn 
        WHERE lending.student_id = :student_id 
        ORDER BY lending.lending_date DESC";
    // $db->pdo を使ってプリペアドステートメントを作成
    $stmt = $db->pdo->prepare($sql);
    $stmt->bindValue(':student_id', $student_id, PDO::PARAM_INT);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <title>貸出履歴 | 図書システム</title>
    <style>
        body {
            font-family: "Helvetica Neue", Arial, "Hiragino Kaku Gothic ProN", "Hiragino Sans", Meiryo, sans-serif;
            background-color: #f0f2f5; /* マイページと同じ背景色 */
            margin: 0;
            padding: 40px 20px;
            display: flex;
            justify-content: center;
        }

        .container {
            width: 100%;
            max-width: 1000px;
            background-color: #fff;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            border-radius: 12px;
            box-sizing: border-box;
        }

        .page-header {
            border-left: 5px solid #1a73e8;
            padding-left: 15px;
            margin-bottom: 30px;
        }

        .page-header h2 {
            margin: 0;
            color: #2c3e50;
            font-size: 22px;
        }

        /* テーブルのデザイン */
        .history-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 15px;
        }

        .history-table th, .history-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .history-table th {
            background-color: #f8fbff;
            color: #444;
            font-weight: bold;
            white-space: nowrap;
        }

        .history-table tr:hover {
            background-color: #f9f9f9;
        }

        /* ステータスバッジ */
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 13px;
            font-weight: bold;
        }
        .status-ok { background-color: #e6fffa; color: #00b894; } /* 緑 */
        .status-warning { background-color: #fff7e6; color: #faad14; } /* オレンジ */
        .status-danger { background-color: #fff1f0; color: #f5222d; } /* 赤 */

        /* 戻るボタン */
        .btn-back {
            display: inline-block;
            background-color: #6c757d;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 20px;
            margin-top: 30px;
            font-size: 14px;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        .btn-back:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <? if ($error_message != null): ?>
            <div style="color: red; padding: 20px; border: 1px solid red; background-color: #fee; text-align: center;">
                <h3>現在システムをご利用いただけません</h3>
                <p><?php echo h($error_message); ?></p>
            </div>
        <?php else: ?>
    <div class="container">
        <div class="page-header">
            <h2>📘 <?php echo htmlspecialchars($student_name, ENT_QUOTES); ?> さんの貸出履歴</h2>
        </div>

        <?php if (count($results) > 0): ?>
        <table class="history-table">
            <thead>
                <tr>
			<th style="width: 20%;">書籍名</th>
                <th style="width: 15%;">書籍ID</th>
        		<th style="width: 15%;">著者</th>
        		<th style="width: 15%;">出版社</th>
        		<th class="text-center" style="width: 20%;">貸出日</th>
        		<th class="text-center" style="width: 20%;">返却日/期限日</th>
        		<th class="text-center" style="width: 10%;">状況</th>
                </tr>
            </thead>
	<tbody>
                <?php
                $today = new DateTime();
                $today->setTime(0, 0, 0);

                foreach ($results as $row): 
                    // 1. 貸出日を取得
                    $lendingDate = new DateTime($row['lending_date']);
                    $lendingDate->setTime(0, 0, 0);

                    // 2. 期限日を計算（貸出日 + 7日）
                    $deadlineDate = clone $lendingDate;
                    $deadlineDate->modify('+7 days');

                    // 3. 実際に返却された日があるかチェック
                    // （データベースの return_date が空でなければ「返却済み」とみなす）
                    $actualReturnDate = $row['return_date'];

                    if (!empty($actualReturnDate)) {
                        // ■ パターンA：もう返している場合
                        $display_date = $actualReturnDate; // 実際に返した日を表示
                        $status_label = "返却済み";
                        // グレー（落ち着いた色）
                        $my_style = "background-color: #6c757d !important; color: white !important;"; 
                    } else {
                        // ■ パターンB：まだ返していない場合（期限計算）
                        $display_date = $deadlineDate->format('Y-m-d'); // 期限日を表示
                        
                        // 今日の日付と期限を比べる
                        $interval = $today->diff($deadlineDate);
                        $days = $interval->days;
                        $invert = $interval->invert; // 期限を過ぎていたら 1 になる

                        if ($invert == 1) {
                            $status_label = "延滞 " . $days . "日";
                            // 赤色
                            $my_style = "background-color: red !important; color: white !important;";
                        } elseif ($days == 0) {
                            $status_label = "今日まで";
                            // 黄色
                            $my_style = "background-color: gold !important; color: black !important;";
                        } else {
                            $status_label = "あと " . $days . "日";
                            // 緑色
                            $my_style = "background-color: green !important; color: white !important;";
                        }
                    }
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                    <td><?php echo htmlspecialchars($row['book_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['author_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['publisher']); ?></td>
                    <td class="text-center"><?php echo htmlspecialchars($row['lending_date']); ?></td>
                    
                    <td class="text-center"><?php echo htmlspecialchars($display_date); ?></td>
                    
                    <td class="text-center">
                        <span class="badge" style="<?php echo $my_style; ?>">
                            <?php echo $status_label; ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
           </tbody>
        <?php else: ?>
            <p style="padding: 40px; text-align: center; color: #888; background: #fafafa; border-radius: 8px;">
                貸出履歴はありません。
            </p>
        <?php endif; ?>
	</table>
        <div style="text-align: center;">
            <a href="stu_myPage.php" class="btn-back">マイページに戻る</a>
        </div>
    </div>

</body>
<?php endif; ?>
</html>