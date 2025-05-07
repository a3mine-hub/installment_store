<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$stmt = $pdo->query("SELECT * FROM products");
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>المتجر الإلكتروني للتقسيط</title>
    <style>
        :root {
            --primary-color: #6C63FF;
            --secondary-color: #4D44DB;
            --light-color: #F8F9FA;
            --dark-color: #212529;
            --gray-color: #6c757d;
            --success-color: #28a745;
        }
        
        body {
            font-family: 'Tajawal', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: var(--dark-color);
            min-height: 100vh;
        }
        
        header {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 100;
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
        
        .welcome-section {
            text-align: center;
            margin: 30px 0;
            padding: 25px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }
        
        .welcome-section h2 {
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .products-title {
            text-align: center;
            color: var(--dark-color);
            margin: 40px 0 30px;
            position: relative;
        }
        
        .products-title::after {
            content: '';
            display: block;
            width: 80px;
            height: 3px;
            background: var(--primary-color);
            margin: 15px auto 0;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .product-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
        
        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .product-details {
            padding: 20px;
        }
        
        .product-details h4 {
            margin-top: 0;
            color: var(--dark-color);
            font-size: 18px;
        }
        
        .product-details p {
            color: var(--gray-color);
            font-size: 14px;
            line-height: 1.5;
        }
        
        .price {
            color: var(--primary-color);
            font-weight: bold;
            font-size: 16px;
            margin: 10px 0;
        }
        
        .installment {
            color: var(--success-color);
            font-size: 14px;
            margin-bottom: 15px;
        }
        
        .btn {
            display: inline-block;
            background: var(--primary-color);
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: background 0.3s;
            width: 100%;
            text-align: center;
            box-sizing: border-box;
        }
        
        .btn:hover {
            background: var(--secondary-color);
        }
        
        footer {
            background: var(--dark-color);
            color: white;
            text-align: center;
            padding: 20px 0;
            margin-top: 50px;
        }
        
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
            }
            
            nav ul {
                margin-top: 15px;
            }
            
            .products-grid {
                grid-template-columns: 1fr;
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
        <section class="welcome-section">
            <h2>مرحباً <?php echo htmlspecialchars($_SESSION['full_name']); ?></h2>
            <p>تصفح أحدث المنتجات المتاحة للتقسيط</p>
        </section>
        
        <h3 class="products-title">المنتجات المتاحة للتقسيط</h3>
        
        <div class="products-grid">
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                <img src="images/products/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image">
                        <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                        <p><?php echo htmlspecialchars($product['description']); ?></p>
                        <div class="price">السعر: <?php echo number_format($product['price'], 2); ?>د.ج</div>
                        <div class="installment">التقسيط: <?php echo htmlspecialchars($product['installment_months']); ?> شهر</div>
                        <a href="product.php?id=<?php echo $product['id']; ?>" class="btn">التفاصيل والشراء</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>جميع الحقوق محفوظة &copy; <?php echo date('Y'); ?></p>
        </div>
    </footer>
</body>
</html>