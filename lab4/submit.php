<?php
$errors = [];
$values = [];
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $values = $_POST;

    if (!preg_match("/^[a-zA-Zа-яА-Я\s]+$/u", $_POST["fio"])) {
        $errors["fio"] = "Допустимы только буквы и пробелы.";
    }

    if (!preg_match("/^\+?[0-9]{10,15}$/", $_POST["phone"])) {
        $errors["phone"] = "Допустимы только цифры, от 10 до 15 символов.";
    }

    if (!preg_match("/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/", $_POST["email"])) {
        $errors["email"] = "Некорректный формат email.";
    }

    if (!preg_match("/^[a-zA-Zа-яА-Я0-9\s.,!?-]+$/u", $_POST["biography"])) {
        $errors["biography"] = "Допустимы буквы, цифры и знаки . , ! ? -";
    }

    if (empty($errors)) {
        $success = true;
        $values = [];
    }
}
?>