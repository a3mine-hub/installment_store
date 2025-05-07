<?php
include 'config.php';
session_start();

// التحقق من وجود معرف المنتج في الرابط
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}
$product_id = intval($_GET['id']);

// جلب بيانات المنتج
try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        header("Location: index.php");
        exit();
    }
} catch (PDOException $e) {
    die("حدث خطأ في جلب بيانات المنتج: " . $e->getMessage());
}

// معالجة عملية الشراء
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['buy'])) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
    
    $quantity = intval($_POST['quantity']);
    $installment_plan = $_POST['installment_plan'];
    
    if ($quantity < 1) {
        $error = "الكمية يجب أن تكون 1 على الأقل";
    } else {
        try {
            $total_price = $product['price'] * $quantity;
            
            $stmt = $pdo->prepare("
                INSERT INTO orders 
                (user_id, product_id, quantity, total_price, installment_plan, status) 
                VALUES (?, ?, ?, ?, ?, 'pending')
            ");
            $stmt->execute([
                $_SESSION['user_id'],
                $product_id,
                $quantity,
                $total_price,
                $installment_plan
            ]);
            
            $success = "تم إضافة المنتج إلى طلباتك بنجاح!";
        } catch (PDOException $e) {
            $error = "حدث خطأ أثناء عملية الشراء: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - متجر التقسيط</title>
    <style>
        :root {
            --primary-color: #6C63FF;
            --secondary-color: #4D44DB;
            --light-color: #F8F9FA;
            --dark-color: #212529;
            --gray-color: #6c757d;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
        }
        
        body {
            font-family: 'Tajawal', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
            color: var(--dark-color);
        }
        
        header {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 15px 0;
        }
        
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px 0;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            color: var(--primary-color);
            font-size: 24px;
            font-weight: 700;
            text-decoration: none;
        }
        
        nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
        }
        
        nav ul li {
            margin-right: 20px;
        }
        
        nav ul li a {
            color: var(--dark-color);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        nav ul li a:hover {
            color: var(--primary-color);
        }
        
        .product-detail {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            padding: 30px;
            margin: 30px 0;
        }
        
        .product-images {
            position: relative;
        }
        
        .main-image {
            width: 100%;
            height: 400px;
            object-fit: contain;
            background: var(--light-color);
            border-radius: 8px;
            margin-bottom: 15px;
        }
        
        .thumbnail-container {
            display: flex;
            gap: 10px;
        }
        
        .thumbnail {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 5px;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.3s;
        }
        
        .thumbnail:hover {
            border-color: var(--primary-color);
        }
        
        .product-info h1 {
            color: var(--dark-color);
            margin-top: 0;
        }
        
        .price {
            font-size: 24px;
            color: var(--primary-color);
            font-weight: bold;
            margin: 15px 0;
        }
        
        .installment-options {
            margin: 20px 0;
        }
        
        .installment-option {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .installment-option input {
            margin-left: 10px;
        }
        
        .quantity-selector {
            display: flex;
            align-items: center;
            margin: 20px 0;
        }
        
        .quantity-selector button {
            background: var(--light-color);
            border: none;
            width: 30px;
            height: 30px;
            font-size: 18px;
            cursor: pointer;
            border-radius: 4px;
        }
        
        .quantity-selector input {
            width: 50px;
            text-align: center;
            margin: 0 10px;
            height: 30px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .btn {
            display: inline-block;
            background: var(--primary-color);
            color: white;
            padding: 12px 25px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: background 0.3s;
            border: none;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            text-align: center;
        }
        
        .btn:hover {
            background: var(--secondary-color);
        }
        
        .btn-outline {
            background: transparent;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
        }
        
        .btn-outline:hover {
            background: var(--primary-color);
            color: white;
        }
        
        .description {
            margin: 30px 0;
            line-height: 1.6;
        }
        
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
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
        
        @media (max-width: 768px) {
            .product-detail {
                grid-template-columns: 1fr;
            }
            
            .header-content {
                flex-direction: column;
            }
            
            nav ul {
                margin-top: 15px;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <a href="index.php" class="logo">متجر التقسيط الإلكتروني</a>
                <nav>
                    <ul>
                        <li><a href="index.php">الرئيسية</a></li>
                        <li><a href="profile.php">حسابي</a></li>
                        <li><a href="logout.php">تسجيل الخروج</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main class="container">
        <?php if(isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if(isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="product-detail">
            <div class="product-images">
            <img src="images/products/<?php echo htmlspecialchars($product['image'] ?? 'default.jpg'); ?>" 

                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                     class="main-image" id="mainImage">
                
                <div class="thumbnail-container">
                <img src="images/products/<?php echo htmlspecialchars($product['image'] ?? 'default.jpg'); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                         class="thumbnail" onclick="changeImage(this)">
                </div>
            </div>
            
            <div class="product-info">
                <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                
                <div class="price">
                    <?php echo number_format($product['price'], 2); ?> د.ج
                </div>
                
                <form method="post">
                    <div class="installment-options">
                        <h3>خطط التقسيط:</h3>
                        
                        <div class="installment-option">
                            <input type="radio" id="plan1" name="installment_plan" value="3 أشهر" checked>
                            <label for="plan1">3 أشهر - <?php echo number_format($product['price'] / 3, 2); ?> د.ج شهرياً</label>
                        </div>
                        
                        <div class="installment-option">
                            <input type="radio" id="plan2" name="installment_plan" value="6 أشهر">
                            <label for="plan2">6 أشهر - <?php echo number_format($product['price'] / 6, 2); ?> د.ج شهرياً</label>
                        </div>
                        
                        <div class="installment-option">
                            <input type="radio" id="plan3" name="installment_plan" value="12 شهر">
                            <label for="plan3">12 شهر - <?php echo number_format($product['price'] / 12, 2); ?> د.ج شهرياً</label>
                        </div>
                    </div>
                    
                    <div class="quantity-selector">
                        <button type="button" onclick="decreaseQuantity()">-</button>
                        <input type="number" id="quantity" name="quantity" value="1" min="1">
                        <button type="button" onclick="increaseQuantity()">+</button>
                    </div>
                    
                    <button type="submit" name="buy" class="btn">شراء الآن</button>
                </form>
                
                <div class="description">
                    <h3>وصف المنتج:</h3>
                    <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                </div>
            </div>
        </div>
    </main>

    <script>
        function changeImage(element) {
            document.getElementById('mainImage').src = element.src;
        }
        
        function increaseQuantity() {
            const quantityInput = document.getElementById('quantity');
            quantityInput.value = parseInt(quantityInput.value) + 1;
        }
        
        function decreaseQuantity() {
            const quantityInput = document.getElementById('quantity');
            if (parseInt(quantityInput.value) > 1) {
                quantityInput.value = parseInt(quantityInput.value) - 1;
            }
        }
    </script>
</body>
</html>