<?php
include 'config.php';
session_start();

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// جلب بيانات المستخدم
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$error = '';
$success = '';

// معالجة تغيير كلمة المرور
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // التحقق من صحة المدخلات
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'جميع الحقول مطلوبة';
    } elseif ($new_password !== $confirm_password) {
        $error = 'كلمة المرور الجديدة غير متطابقة';
    } elseif (strlen($new_password) < 8) {
        $error = 'كلمة المرور يجب أن تكون 8 أحرف على الأقل';
    } elseif (!password_verify($current_password, $user['password'])) {
        $error = 'كلمة المرور الحالية غير صحيحة';
    } else {
        try {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed_password, $_SESSION['user_id']]);
            
            $success = 'تم تغيير كلمة المرور بنجاح';
        } catch (PDOException $e) {
            $error = 'حدث خطأ أثناء تحديث كلمة المرور: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تغيير كلمة المرور</title>
    <style>
        body {
            font-family: 'Tajawal', Arial, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8eb 100%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }
        
        header {
            background-color: #2c3e50;
            color: white;
            padding: 20px 0;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        header .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        header h1 {
            margin: 0;
            font-size: 1.8rem;
        }
        
        nav ul {
            list-style: none;
            display: flex;
            margin: 0;
            padding: 0;
        }
        
        nav ul li {
            margin-left: 25px;
        }
        
        nav ul li a {
            color: white;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s;
            padding: 8px 12px;
            border-radius: 5px;
        }
        
        nav ul li a:hover {
            background-color: rgba(255,255,255,0.1);
        }
        
        .password-card {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 90%;
            max-width: 600px;
            margin: 40px auto;
        }
        
        .password-title {
            color: #2c3e50;
            text-align: center;
            font-size: 2rem;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 10px;
            color: #555;
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .form-group input {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1.1rem;
            box-sizing: border-box;
            transition: all 0.3s;
        }
        
        .form-group input:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 4px rgba(52, 152, 219, 0.2);
            outline: none;
        }
        
        .btn {
            background: linear-gradient(to right, #3498db, #2980b9);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 16px;
            font-size: 1.2rem;
            cursor: pointer;
            width: 100%;
            display: block;
            margin-top: 30px;
            transition: all 0.3s;
            font-weight: 600;
        }
        
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(52, 152, 219, 0.4);
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            text-align: center;
            font-size: 1.1rem;
        }
        
        .alert.error {
            background-color: #ffebee;
            color: #c62828;
            border: 1px solid #ef9a9a;
        }
        
        .alert.success {
            background-color: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #a5d6a7;
        }
        
        footer {
            background-color: #2c3e50;
            color: white;
            text-align: center;
            padding: 25px 0;
            margin-top: 50px;
        }
        
        @media (max-width: 768px) {
            header .container {
                flex-direction: column;
            }
            
            nav ul {
                margin-top: 15px;
            }
            
            nav ul li {
                margin: 0 10px;
            }
            
            .password-card {
                padding: 25px;
                width: 95%;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>متجر التقسيط الإلكتروني</h1>
            <nav>
                <ul>
                    <li><a href="index.php">الرئيسية</a></li>
                    <li><a href="profile.php">حسابي</a></li>
                    <li><a href="logout.php">تسجيل الخروج</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <div class="password-card">
            <h1 class="password-title">تغيير كلمة المرور</h1>
            
            <?php if ($error): ?>
                <div class="alert error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="post">
                <div class="form-group">
                    <label for="current_password">كلمة المرور الحالية:</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>
                
                <div class="form-group">
                    <label for="new_password">كلمة المرور الجديدة:</label>
                    <input type="password" id="new_password" name="new_password" required minlength="8">
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">تأكيد كلمة المرور الجديدة:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
                </div>
                
                <button type="submit" class="btn">تغيير كلمة المرور</button>
            </form>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>جميع الحقوق محفوظة &copy; <?php echo date('Y'); ?></p>
        </div>
    </footer>
</body>
</html>