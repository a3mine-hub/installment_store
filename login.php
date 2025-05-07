<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول</title>
     <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f0f8ff; /* لون أزرق فاتح */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            padding: 20px;
        }
        
        .login-box {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            padding: 30px;
            width: 90%;
            max-width: 400px;
            text-align: center;
        }
        
        .login-title {
            color: #2c3e50;
            font-size: 24px;
            margin-bottom: 30px;
            font-weight: bold;
        }
        
        .input-field {
            width: 100%;
            padding: 12px 15px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            box-sizing: border-box;
            text-align: right;
        }
        
        .login-btn {
            background-color: #4682b4; /* لون أزرق */
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px 0;
            font-size: 18px;
            cursor: pointer;
            width: 100%;
            margin: 20px 0;
            transition: background-color 0.3s;
        }
        
        .login-btn:hover {
            background-color: #3a6d99;
        }
        
        .footer-links {
            margin-top: 20px;
            font-size: 14px;
            color: #555;
        }
        
        .footer-links a {
            color: #4682b4;
            text-decoration: none;
            margin: 0 10px;
        }
        
        .footer-links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <div class="login-title">تسجيل الدخول</div>
        
        <?php if(isset($_GET['error'])): ?>
        <div style="color:red; margin-bottom:15px; text-align:center;">
            <?php 
            if($_GET['error'] == 'invalid_credentials') {
                echo "اسم المستخدم أو كلمة المرور غير صحيحة";
            }
            ?>
        </div>
        <?php endif; ?>
        
        <form action="login_process.php" method="post">
            <input type="text" name="username" class="input-field" placeholder="اسم المستخدم أو البريد الإلكتروني" required>
            
            <input type="password" name="password" class="input-field" placeholder="كلمة المرور" required>
            
            <button type="submit" class="login-btn">تسجيل الدخول</button>
        </form>
        
        <div class="footer-links">
            <a href="forgot_password.php">نسيت كلمة المرور؟</a>
            <a href="register.php">إنشاء حساب جديد</a>
        </div>
    </div>
</body>
</html>