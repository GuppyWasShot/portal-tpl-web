<?php
/**
 * Entry Point untuk Admin Panel
 * Redirect ke views/admin/login.php atau index.php
 */
session_start();

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: views/admin/index.php");
} else {
    header("Location: views/admin/login.php");
}
exit();

