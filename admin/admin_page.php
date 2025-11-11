<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login_register/purdex.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Panel - Purrfect Paws</title>
<link rel="stylesheet" href="../HTML/css/admin.css">
<link href="https://fonts.googleapis.com/css2?family=Kaushan+Script&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<script src="https://kit.fontawesome.com/df5d6157cf.js" crossorigin="anonymous"></script>
</head>
<body>
<div class="layout">

    <!-- Sidebar -->
    <aside class="sidebar">
        <h2 class="logo">Purrfect Paws</h2>
        <div class="profile">
            <img src="../HTML/images/mwehehehe.jpg" alt="Admin User" onerror="this.onerror=null; this.src='../HTML/images/mwehehehe.jpg'">
            <span>Admin</span>
        </div>
        <ul class="menu">
            <li class="active" data-section="dashboard"><i class="fa fa-home"></i> <span>Dashboard</span></li>
            <li data-section="inventory"><i class="fa fa-box"></i> <span>Inventory</span></li>
            <li data-section="orders"><i class="fa fa-file-invoice"></i> <span>Orders</span></li>
            <li data-section="users"><i class="fa fa-users"></i> <span>Users</span></li>
            <li data-section="analytics"><i class="fa fa-chart-bar"></i> <span>Analytics</span></li>
            <li data-section="reports"><i class="fa fa-chart-line"></i> <span>Reports</span></li>
        </ul>
    </aside>

    <!-- Content -->
    <main class="content">
        <header class="topbar">
            <div class="topbar-left">
                <button id="toggleSidebar" title="Toggle Sidebar"><i class="fa-solid fa-bars"></i></button>
            </div>
            <a href="../login_register/logout.php" class="logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </header>

        <section id="mainContent" class="panel">
            <div style="text-align: center; padding: 40px;">
                <h2>Loading...</h2>
            </div>
        </section>
    </main>
</div>

<script src="../js/admin.js?v=orders-v2" defer></script>
</body>
</html>
