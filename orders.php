<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// جلب هيكل الجدول أولاً لتحديد الأعمدة المتاحة
try {
    $table_info = $pdo->query("DESCRIBE orders")->fetchAll(PDO::FETCH_COLUMN);
    
    // بناء استعلام ديناميكي بناءً على الأعمدة المتاحة
    $columns = [];
    $select_fields = [];
    
    if (in_array('id', $table_info)) {
        $select_fields[] = 'id AS order_id';
    } elseif (in_array('order_id', $table_info)) {
        $select_fields[] = 'order_id';
    }
    
    if (in_array('created_at', $table_info)) {
        $select_fields[] = 'created_at AS order_date';
    } elseif (in_array('order_date', $table_info)) {
        $select_fields[] = 'order_date';
    } elseif (in_array('date', $table_info)) {
        $select_fields[] = 'date AS order_date';
    }
    
    if (in_array('total', $table_info)) {
        $select_fields[] = 'total AS total_amount';
    } elseif (in_array('total_amount', $table_info)) {
        $select_fields[] = 'total_amount';
    } elseif (in_array('amount', $table_info)) {
        $select_fields[] = 'amount AS total_amount';
    }
    
    if (in_array('status', $table_info)) {
        $select_fields[] = 'status';
    }
    
    if (empty($select_fields)) {
        throw new Exception("لا يمكن تحديد حقول الطلب في الجدول");
    }
    
    $query = "SELECT " . implode(', ', $select_fields) . " FROM orders WHERE user_id = ? ORDER BY ";
    
    if (in_array('order_date', $table_info)) {
        $query .= "order_date DESC";
    } elseif (in_array('created_at', $table_info)) {
        $query .= "created_at DESC";
    } elseif (in_array('date', $table_info)) {
        $query .= "date DESC";
    } else {
        $query .= "id DESC";
    }
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$_SESSION['user_id']]);
    $orders = $stmt->fetchAll();
    
} catch (PDOException $e) {
    die("حدث خطأ في جلب الطلبات: " . $e->getMessage());
} catch (Exception $e) {
    die($e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>طلباتي - متجر التقسيط</title>
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --success-color: #2ecc71;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --light-color: #ecf0f1;
            --dark-color: #34495e;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Tajawal', sans-serif;
            background-color: #f8f9fa;
            color: #333;
            line-height: 1.6;
        }
        
        .header {
            background-color: var(--secondary-color);
            color: white;
            padding: 1.5rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 1.8rem;
            font-weight: 700;
        }
        
        .nav-links {
            display: flex;
            list-style: none;
        }
        
        .nav-links li {
            margin-right: 1.5rem;
        }
        
        .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: all 0.3s;
        }
        
        .nav-links a:hover {
            background-color: rgba(255,255,255,0.1);
        }
        
        .orders-container {
            padding: 3rem 0;
        }
        
        .page-title {
            text-align: center;
            margin-bottom: 2rem;
            color: var(--secondary-color);
            font-size: 2rem;
            position: relative;
            padding-bottom: 1rem;
        }
        
        .page-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 3px;
            background-color: var(--primary-color);
        }
        
        .orders-list {
            display: grid;
            gap: 1.5rem;
        }
        
        .order-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .order-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .order-header {
            background-color: var(--light-color);
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #eee;
        }
        
        .order-id {
            font-weight: 700;
            color: var(--secondary-color);
        }
        
        .order-date {
            color: #666;
            font-size: 0.9rem;
        }
        
        .order-body {
            padding: 1.5rem;
        }
        
        .order-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
        }
        
        .order-items {
            color: #666;
        }
        
        .order-total {
            font-weight: 700;
            color: var(--secondary-color);
        }
        
        .order-status {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-processing {
            background-color: #cce5ff;
            color: #004085;
        }
        
        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .order-actions {
            margin-top: 1.5rem;
            display: flex;
            justify-content: flex-end;
        }
        
        .btn {
            display: inline-block;
            padding: 0.6rem 1.2rem;
            border: none;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--primary-color);
            color: var(--primary-color);
        }
        
        .btn-outline:hover {
            background-color: var(--primary-color);
            color: white;
        }
        
        .no-orders {
            text-align: center;
            padding: 3rem;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        }
        
        .no-orders-icon {
            font-size: 3rem;
            color: #ddd;
            margin-bottom: 1rem;
        }
        
        .footer {
            background-color: var(--secondary-color);
            color: white;
            text-align: center;
            padding: 1.5rem 0;
            margin-top: 2rem;
        }
        
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
            }
            
            .nav-links {
                margin-top: 1rem;
            }
            
            .nav-links li {
                margin-right: 0;
                margin-left: 1rem;
            }
            
            .order-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
            
            .order-details {
                flex-direction: column;
                gap: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container header-content">
            <div class="logo">متجر التقسيط</div>
            <ul class="nav-links">
                <li><a href="index.php">الرئيسية</a></li>
                <li><a href="profile.php">حسابي</a></li>
                <li><a href="logout.php">تسجيل الخروج</a></li>
            </ul>
        </div>
    </header>

    <main class="orders-container">
        <div class="container">
            <h1 class="page-title">طلباتي</h1>
            
            <div class="orders-list">
                <?php if (!empty($orders)): ?>
                    <?php foreach ($orders as $order): ?>
                        <div class="order-card">
                            <div class="order-header">
                                <span class="order-id">طلب #<?php echo $order['order_id'] ?? $order['id'] ?? 'N/A'; ?></span>
                                <span class="order-date">
                                    <?php 
                                    $date = $order['order_date'] ?? $order['created_at'] ?? $order['date'] ?? '';
                                    echo $date ? date('Y/m/d', strtotime($date)) : 'غير محدد';
                                    ?>
                                </span>
                            </div>
                            
                            <div class="order-body">
                                <div class="order-details">
                                    <span class="order-total">
                                        <?php 
                                        $amount = $order['total_amount'] ?? $order['total'] ?? $order['amount'] ?? 0;
                                        echo number_format((float)$amount, 2) . ' د.ج'; 
                                        ?>
                                    </span>
                                </div>
                                
                                <?php if (isset($order['status'])): ?>
                                <?php
                                $status_class = '';
                                $status_text = $order['status'];
                                switch (strtolower($order['status'])) {
                                    case 'pending':
                                    case 'قيد الانتظار':
                                        $status_class = 'status-pending';
                                        $status_text = 'قيد الانتظار';
                                        break;
                                    case 'processing':
                                    case 'قيد التجهيز':
                                        $status_class = 'status-processing';
                                        $status_text = 'قيد التجهيز';
                                        break;
                                    case 'completed':
                                    case 'مكتمل':
                                        $status_class = 'status-completed';
                                        $status_text = 'مكتمل';
                                        break;
                                    case 'cancelled':
                                    case 'ملغى':
                                        $status_class = 'status-cancelled';
                                        $status_text = 'ملغى';
                                        break;
                                    default:
                                        $status_class = 'status-pending';
                                }
                                ?>
                                <span class="order-status <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                <?php endif; ?>
                                
                                <div class="order-actions">
                                    <a href="order_details.php?id=<?php echo $order['order_id'] ?? $order['id'] ?? ''; ?>" class="btn btn-outline">تفاصيل الطلب</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-orders">
                        <div class="no-orders-icon">🛒</div>
                        <h3>لا توجد طلبات</h3>
                        <p>لم تقم بعمل أي طلبات حتى الآن</p>
                        <a href="index.php" class="btn btn-outline" style="margin-top: 1rem;">تصفح المنتجات</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>جميع الحقوق محفوظة &copy; <?php echo date('Y'); ?> - متجر التقسيط</p>
        </div>
    </footer>
</body>
</html>