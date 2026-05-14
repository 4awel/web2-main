<?php
define('DATA_FILE', __DIR__ . '/users_data.json');

function getAllUsers() {
    if (!file_exists(DATA_FILE)) {
        return [];
    }
    $json = file_get_contents(DATA_FILE);
    return json_decode($json, true) ?: [];
}

function saveAllUsers($users) {
    file_put_contents(DATA_FILE, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function getUserById($id) {
    $users = getAllUsers();
    foreach ($users as $user) {
        if ($user['id'] == $id) {
            return $user;
        }
    }
    return null;
}
// 123
function updateUser($id, $newData) {
    $users = getAllUsers();
    foreach ($users as $key => $user) {
        if ($user['id'] == $id) {
            $users[$key] = array_merge($user, $newData);
            saveAllUsers($users);
            return true;
        }
    }
    return false;
}

function deleteUser($id) {
    $users = getAllUsers();
    foreach ($users as $key => $user) {
        if ($user['id'] == $id) {
            unset($users[$key]);
            saveAllUsers(array_values($users));
            return true;
        }
    }
    return false;
}

function getLanguageStats() {
    $users = getAllUsers();
    $stats = [];
    
    foreach ($users as $user) {
        if (isset($user['languages']) && is_array($user['languages'])) {
            foreach ($user['languages'] as $lang) {
                if (!isset($stats[$lang])) {
                    $stats[$lang] = 0;
                }
                $stats[$lang]++;
            }
        }
    }
    
    arsort($stats);
    return $stats;
}
?>