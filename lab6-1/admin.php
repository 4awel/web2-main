<?php
require_once 'config.php';

// HTTP авторизация
if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
    header('WWW-Authenticate: Basic realm="Admin Access"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Требуется авторизация';
    exit;
}

$valid_username = 'admin';
$valid_password = 'admin123';

if ($_SERVER['PHP_AUTH_USER'] != $valid_username || $_SERVER['PHP_AUTH_PW'] != $valid_password) {
    header('WWW-Authenticate: Basic realm="Admin Access"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Неверный логин или пароль';
    exit;
}

// Обработка POST запросов
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete'])) {
        deleteUser($_POST['delete']);
        header('Location: admin.php');
        exit;
    }
    
    if (isset($_POST['edit'])) {
        $languages = isset($_POST['languages']) ? (array)$_POST['languages'] : [];
        updateUser($_POST['edit'], [
            'name' => htmlspecialchars($_POST['name']),
            'email' => htmlspecialchars($_POST['email']),
            'languages' => $languages
        ]);
        header('Location: admin.php');
        exit;
    }
}

$users = getAllUsers();
$stats = getLanguageStats();
$editUser = null;

if (isset($_GET['edit'])) {
    $editUser = getUserById($_GET['edit']);
}

$availableLanguages = ['PHP', 'JavaScript', 'Python', 'Java', 'C++', 'C#', 'Ruby', 'Go', 'Swift', 'Kotlin'];
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        .header {
            background: white;
            border-radius: 15px;
            padding: 20px 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 { color: #667eea; font-size: 28px; }
        .stats-section, .users-section {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .stats-section h2, .users-section h2 {
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            transition: transform 0.3s;
        }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-language { font-size: 20px; font-weight: bold; margin-bottom: 10px; }
        .stat-count { font-size: 36px; font-weight: bold; }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        th {
            background: #f5f5f5;
            color: #333;
            font-weight: 600;
        }
        tr:hover { background: #f9f9f9; }
        .lang-badge {
            background: #667eea;
            color: white;
            padding: 3px 8px;
            border-radius: 5px;
            font-size: 12px;
            display: inline-block;
            margin: 2px;
        }
        .btn-edit, .btn-delete {
            padding: 5px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            text-decoration: none;
            display: inline-block;
        }
        .btn-edit { background: #4CAF50; color: white; }
        .btn-delete { background: #f44336; color: white; }
        .modal {
            display: <?php echo $editUser ? 'flex' : 'none'; ?>;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
        }
        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .checkbox-group {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            max-height: 200px;
            overflow-y: auto;
        }
        .checkbox-group label {
            display: flex;
            align-items: center;
            gap: 5px;
            margin: 0;
        }
        .modal-buttons {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
        }
        .btn-submit { background: #667eea; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        .btn-cancel { background: #999; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>📊 Панель администратора</h1>
        <div>Вы вошли как: <strong><?php echo htmlspecialchars($_SERVER['PHP_AUTH_USER']); ?></strong></div>
    </div>
    
    <div class="stats-section">
        <h2>Статистика по языкам программирования</h2>
        <div class="stats-grid">
            <?php foreach ($stats as $language => $count): ?>
            <div class="stat-card">
                <div class="stat-language"><?php echo htmlspecialchars($language); ?></div>
                <div class="stat-count"><?php echo $count; ?></div>
                <div>пользователей</div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <div class="users-section">
        <h2>Список пользователей</h2>
        <table>
            <thead>
                <tr><th>ID</th><th>Имя</th><th>Email</th><th>Любимые языки</th><th>Действия</th></tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td>
                        <?php foreach ($user['languages'] as $lang): ?>
                        <span class="lang-badge"><?php echo htmlspecialchars($lang); ?></span>
                        <?php endforeach; ?>
                    </td>
                    <td>
                        <a href="?edit=<?php echo $user['id']; ?>" class="btn-edit">✏️ Редактировать</a>
                        <form method="POST" style="display:inline" onsubmit="return confirm('Удалить пользователя?');">
                            <input type="hidden" name="delete" value="<?php echo $user['id']; ?>">
                            <button type="submit" class="btn-delete">🗑️ Удалить</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($editUser): ?>
<div class="modal" style="display: flex;">
    <div class="modal-content">
        <h3>Редактирование пользователя</h3>
        <form method="POST">
            <input type="hidden" name="edit" value="<?php echo $editUser['id']; ?>">
            <div class="form-group">
                <label>Имя:</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($editUser['name']); ?>" required>
            </div>
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($editUser['email']); ?>" required>
            </div>
            <div class="form-group">
                <label>Языки программирования:</label>
                <div class="checkbox-group">
                    <?php foreach ($availableLanguages as $lang): ?>
                    <label>
                        <input type="checkbox" name="languages[]" value="<?php echo $lang; ?>"
                            <?php echo (in_array($lang, $editUser['languages'])) ? 'checked' : ''; ?>>
                        <?php echo $lang; ?>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="modal-buttons">
                <button type="submit" class="btn-submit">💾 Сохранить</button>
                <a href="admin.php" class="btn-cancel">❌ Отмена</a>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>
</body>
</html>