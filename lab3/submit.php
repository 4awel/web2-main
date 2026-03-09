<?php

try {
    $pdo = new PDO('mysql:host=localhost;dbname=u82380', 'u82380', '43t3w4wE$');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (!preg_match("/^[a-zA-Zа-яА-Я\s]{1,150}$/u", $_POST['fio'])) {
        die("Ошибка: некорректное ФИО");
    }

    if (!isset($_POST['languages']) || count($_POST['languages']) == 0) {
        die("Ошибка: выберите хотя бы один язык");
    }

    $stmt = $pdo->prepare("INSERT INTO application 
        (fio, phone, email, birthdate, gender, biography, contract) 
        VALUES (?, ?, ?, ?, ?, ?, ?)");

    $stmt->execute([
        $_POST['fio'],
        $_POST['phone'],
        $_POST['email'],
        $_POST['birthdate'],
        $_POST['gender'],
        $_POST['biography'],
        isset($_POST['contract']) ? 1 : 0
    ]);

    $app_id = $pdo->lastInsertId();

    $stmtLang = $pdo->prepare("INSERT INTO application_language 
        (application_id, language_id) VALUES (?, ?)");

    foreach ($_POST['languages'] as $lang) {
        $stmtLang->execute([$app_id, $lang]);
    }

    echo "Данные успешно сохранены!";

} catch (PDOException $e) {
    echo "Ошибка: " . $e->getMessage();
}
?>