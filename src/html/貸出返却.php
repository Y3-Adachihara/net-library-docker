<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>貸出・返却</title>
    <link rel = "stylesheet" href="../css/貸出返却.css">
</head>
<body>
<div class="container">

    <h1>貸出・返却</h1>
    <form>
        <div class="form-group">
            <label>学年：</label>
            <select name="school-year" required>
                <option value="">選択してください</option>
                <option value="1">1年</option>
                <option value="2">2年</option>
                <option value="3">3年</option>
                <option value="4">4年</option>
                <option value="5">5年</option>
                <option value="6">6年</option>
                <option value="7">7年</option>
                <option value="8">8年</option>
                <option value="9">9年</option>

            </select>
        </div>

        <div class="form-group">
            <label>クラス:</label>
            <input type="text" name="calss" placeholder="1">
        </div>

        <div class="form-group">
    <label>番号：</label>
    <select name="school-year" id="number-select" required>
        <option value="">選択してください</option>
    </select>
</div>

<script>
    const selectElement = document.getElementById('number-select');

    for (let i = 1; i <= 50; i++) {

        const option = document.createElement('option');
    
        option.value = i;
        option.textContent = i;

        selectElement.appendChild(option);
    }
</script>

        <div class="form-group">
            <label>識別番号：</label>
            <input type="text" name="id-number" placeholder="901000101">
        </div>

        <div class="action-buttons">
            <button type="button" class="btn-blue" onclick="location.href='librarian_myPage.php'">戻る</button>
            <button type="button" class="btn-blue">貸出</button>
            <button type="button" class="btn-blue">返却</button>
        </div>
    </form>
</div>
</body>
</html>