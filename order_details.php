<?php
include 'config.php';
session_start();

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// التحقق من وجود معرف الطلب
if (!isset($_GET['id'])) {
    header("Location: orders.php");
    exit();
}

$order_id = intval($_GET['id']);

// معالجة إلغاء الطلب عبر AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order'])) {
    header('Content-Type: application/json');
    
    try {
        // التحقق من أن الطلب مملوك للمستخدم الحالي
        $stmt = $pdo->prepare("SELECT id, status FROM orders WHERE id = ? AND user_id = ?");
        $stmt->execute([$order_id, $_SESSION['user_id']]);
        $order = $stmt->fetch();
        
        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'لا يمكن إلغاء هذا الطلب']);
            exit();
        }
        
        if ($order['status'] !== 'pending') {
            echo json_encode(['success' => false, 'message' => 'لا يمكن إلغاء الطلب في حالته الحالية']);
            exit();
        }
        
        // تحديث حالة الطلب إلى "ملغى"
        $stmt = $pdo->prepare("UPDATE orders SET status = 'cancelled', cancelled_at = NOW() WHERE id = ?");
        $stmt->execute([$order_id]);
        
        echo json_encode(['success' => true, 'message' => 'تم إلغاء الطلب بنجاح']);
        exit();
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'حدث خطأ في إلغاء الطلب: ' . $e->getMessage()]);
        exit();
    }
}

// جلب بيانات الطلب
try {
    // معلومات الطلب الأساسية
    $stmt = $pdo->prepare("
        SELECT o.*, u.full_name, u.phone, u.address 
        FROM orders o
        JOIN users u ON o.user_id = u.id
        WHERE o.id = ? AND o.user_id = ?
    ");
    $stmt->execute([$order_id, $_SESSION['user_id']]);
    $order = $stmt->fetch();
    
    if (!$order) {
        header("Location: orders.php");
        exit();
    }
    
    // تفاصيل المنتجات في الطلب
    $stmt = $pdo->prepare("
        SELECT oi.*, p.name, p.image, p.price
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$order_id]);
    $order_items = $stmt->fetchAll();
    
} catch (PDOException $e) {
    die("حدث خطأ في جلب تفاصيل الطلب: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تفاصيل الطلب #<?php echo $order_id; ?></title>
    <style>
        :root {
            --primary: #3498db;
            --secondary: #2c3e50;
            --light: #f8f9fa;
            --dark: #343a40;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
        }
        
        body {
            font-family: 'Tajawal', sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
            color: #333;
        }
        
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px 0;
        }
        
        .order-details-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #eee;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        
        .order-title {
            color: var(--secondary);
            margin: 0;
        }
        
        .order-status {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
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
        
        .order-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .info-card {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
        }
        
        .info-card h4 {
            margin-top: 0;
            color: var(--secondary);
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        
        .info-label {
            font-weight: 600;
            color: #666;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        .items-table th {
            background: var(--secondary);
            color: white;
            padding: 12px;
            text-align: right;
        }
        
        .items-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        
        .items-table tr:hover {
            background: #f5f5f5;
        }
        
        .product-cell {
            display: flex;
            align-items: center;
        }
        
        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
            margin-left: 15px;
        }
        
        .total-section {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
        }
        
        .total-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            width: 300px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 16px;
        }
        
        .grand-total {
            font-weight: 700;
            font-size: 18px;
            color: var(--secondary);
            border-top: 1px solid #ddd;
            padding-top: 10px;
            margin-top: 10px;
        }
        
        .action-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            cursor: pointer;
            border: none;
            font-family: 'Tajawal', sans-serif;
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background: #2185d0;
        }
        
        .btn-outline {
            border: 1px solid var(--primary);
            color: var(--primary);
            background: white;
        }
        
        .btn-outline:hover {
            background: #f5f5f5;
        }
        
        .btn-danger {
            background: var(--danger);
            color: white;
        }
        
        .btn-danger:hover {
            background: #c82333;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            display: none;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .btn-cancel {
            position: relative;
            transition: all 0.3s;
        }
        
        .btn-cancel.loading {
            opacity: 0.7;
            pointer-events: none;
        }
        
        .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
            margin-right: 10px;
            vertical-align: middle;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        @media (max-width: 768px) {
            .order-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .order-status {
                margin-top: 10px;
            }
            
            .product-cell {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .product-image {
                margin-left: 0;
                margin-bottom: 10px;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 10px;
            }
            
            .btn {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="alert alert-success" id="successAlert"></div>
        <div class="alert alert-error" id="errorAlert"></div>
        
        <div class="order-details-container">
            <div class="order-header">
                <h1 class="order-title">تفاصيل الطلب #<?php echo $order_id; ?></h1>
                <span class="order-status <?php 
                    echo 'status-' . strtolower($order['status']);
                ?>" id="orderStatusText">
                    <?php 
                    $status_map = [
                        'pending' => 'قيد الانتظار',
                        'processing' => 'قيد التجهيز',
                        'completed' => 'مكتمل',
                        'cancelled' => 'ملغى'
                    ];
                    echo $status_map[strtolower($order['status'])] ?? $order['status'];
                    ?>
                </span>
            </div>
            
            <div class="order-info-grid">
                <div class="info-card">
                    <h4>معلومات العميل</h4>
                    <div class="info-row">
                        <span class="info-label">الاسم:</span>
                        <span><?php echo htmlspecialchars($order['full_name']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">الهاتف:</span>
                        <span><?php echo htmlspecialchars($order['phone']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">العنوان:</span>
                        <span><?php echo htmlspecialchars($order['address']); ?></span>
                    </div>
                </div>
                
                <div class="info-card">
                    <h4>معلومات الطلب</h4>
                    <div class="info-row">
                        <span class="info-label">تاريخ الطلب:</span>
                        <span><?php echo date('Y/m/d H:i', strtotime($order['order_date'])); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">طريقة الدفع:</span>
                        <span><?php echo htmlspecialchars($order['payment_method'] ?? 'غير محدد'); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">خطة التقسيط:</span>
                        <span><?php echo htmlspecialchars($order['installment_plan'] ?? 'غير محدد'); ?></span>
                    </div>
                </div>
            </div>
            
            <h3>المنتجات المطلوبة</h3>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>المنتج</th>
                        <th>السعر</th>
                        <th>الكمية</th>
                        <th>المجموع</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order_items as $item): ?>
                    <tr>
                        <td>
                            <div class="product-cell">
                                <div>
                                    <div><?php echo htmlspecialchars($item['name']); ?></div>
                                </div>
                                <?php if(!empty($item['image'])): ?>
                                <img src="uploads/<?php echo htmlspecialchars($item['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                     class="product-image">
                                <?php endif; ?>
                            </div>
                        </td>
                        <td><?php echo number_format($item['price'], 2); ?> دج</td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td><?php echo number_format($item['price'] * $item['quantity'], 2); ?> دج</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="total-section">
                <div class="total-card">
                    <div class="total-row">
                        <span>المجموع الجزئي:</span>
                        <span><?php echo number_format($order['total_price'], 2); ?> دج</span>
                    </div>
                    <div class="total-row">
                        <span>التوصيل:</span>
                        <span><?php echo number_format($order['shipping_fee'] ?? 0, 2); ?> دج</span>
                    </div>
                    <div class="total-row grand-total">
                        <span>المجموع الكلي:</span>
                        <span><?php echo number_format($order['total_price'] + ($order['shipping_fee'] ?? 0), 2); ?> دج</span>
                    </div>
                </div>
            </div>
            
            <div class="action-buttons">
                <a href="orders.php" class="btn btn-outline">العودة إلى الطلبات</a>
                <?php if($order['status'] == 'pending'): ?>
                <button type="button" class="btn btn-danger btn-cancel" id="cancelOrderBtn">
                    <span class="spinner" id="cancelSpinner"></span>
                    إلغاء الطلب
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('cancelOrderBtn').addEventListener('click', function() {
            if (confirm('هل أنت متأكد من إلغاء هذا الطلب؟')) {
                const btn = this;
                const spinner = document.getElementById('cancelSpinner');
                
                // عرض مؤشر التحميل
                spinner.style.display = 'inline-block';
                btn.disabled = true;
                btn.classList.add('loading');
                
                // إرسال طلب الإلغاء
                fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'cancel_order=1'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // عرض رسالة النجاح
                        const successAlert = document.getElementById('successAlert');
                        successAlert.textContent = data.message;
                        successAlert.style.display = 'block';
                        
                        // تحديث حالة الطلب في الواجهة
                        document.getElementById('orderStatusText').textContent = 'ملغى';
                        document.getElementById('orderStatusText').className = 'order-status status-cancelled';
                        
                        // إخفاء زر الإلغاء
                        btn.style.display = 'none';
                    } else {
                        // عرض رسالة الخطأ
                        const errorAlert = document.getElementById('errorAlert');
                        errorAlert.textContent = data.message;
                        errorAlert.style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    const errorAlert = document.getElementById('errorAlert');
                    errorAlert.textContent = 'حدث خطأ أثناء محاولة الإلغاء';
                    errorAlert.style.display = 'block';
                })
                .finally(() => {
                    // إخفاء مؤشر التحميل
                    spinner.style.display = 'none';
                    btn.disabled = false;
                    btn.classList.remove('loading');
                    
                    // إخفاء الرسائل بعد 5 ثواني
                    setTimeout(() => {
                        document.getElementById('successAlert').style.display = 'none';
                        document.getElementById('errorAlert').style.display = 'none';
                    }, 5000);
                });
            }
        });
    </script>
</body>
</html>