<?php
session_start();


if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}


function isAdmin()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}


function requireAdmin()
{
    if (!isAdmin()) {
        $_SESSION['error'] = 'Access denied. Administrator privileges required.';
        header('Location: ../index.php');
        exit;
    }
}


function hasPermission($module_name)
{
    global $pdo;
    
    
    if (isAdmin()) {
        return true;
    }
    
    
    try {
        if (!isset($_SESSION['user_id']) || !isset($pdo)) {
            return false;
        }
        
        $stmt = $pdo->prepare("SELECT 1 FROM user_permission WHERE user_id = ? AND module_name = ?");
        $stmt->execute([$_SESSION['user_id'], $module_name]);
        return $stmt->fetch() !== false;
    } catch (Exception $e) {
        return false;
    }
}


function requirePermission($module_name)
{
    if (!hasPermission($module_name)) {
        $_SESSION['error'] = "Access denied. You do not have permission to view the {$module_name} dashboard.";
        header('Location: ../index.php');
        exit;
    }
}
?>
