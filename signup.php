<?php
session_start();
include 'includes/db.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($username)) {
        $errors[] = 'Username is required.';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required.';
    }
    if (empty($password)) {
        $errors[] = 'Password is required.';
    }
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'Email is already registered.';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            if ($stmt->execute([$username, $email, $hashed_password])) {
                $_SESSION['user'] = $username;
                header('Location: index.php');
                exit;
            } else {
                $errors[] = 'Failed to register user.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Signup</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;400&display=swap" rel="stylesheet">
<style>
        body {
        overflow-x: hidden;
    }

.split-bg {
    display: flex;
    min-height: 100vh;
    width: 100vw;
    background: #e0eafc;
    background: linear-gradient(120deg, #e0eafc 0%, #cfdef3 100%);
}
.left-panel {
    flex: 1.2;
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    color: #fff;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
    min-width: 260px;
}
.logo-area {
    z-index: 2;
    text-align: center;
}
.logo-circle {
    width: 70px;
    height: 70px;
    background: linear-gradient(135deg, #00c6ff, #0072ff);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.2rem;
    font-weight: 900;
    margin: 0 auto 18px auto;
    box-shadow: 0 4px 18px rgba(0,114,255,0.25);
    letter-spacing: 2px;
    font-family: 'Montserrat', sans-serif;
}
.logo-area h1 {
    font-family: 'Montserrat', sans-serif;
    font-size: 2.1rem;
    font-weight: 700;
    margin-bottom: 10px;
    letter-spacing: 1.5px;
}
.logo-area p {
    font-size: 1.1rem;
    font-weight: 400;
    opacity: 0.92;
}
.animated-shapes {
    position: absolute;
    top: 0; left: 0; width: 100%; height: 100%;
    z-index: 1;
    pointer-events: none;
}
.animated-shapes::before, .animated-shapes::after {
    content: '';
    position: absolute;
    border-radius: 50%;
    opacity: 0.18;
    animation: float-shape 7s infinite alternate ease-in-out;
}
.animated-shapes::before {
    width: 180px; height: 180px;
    background: #00c6ff;
    left: 10%; top: 18%;
    animation-delay: 0s;
}
.animated-shapes::after {
    width: 120px; height: 120px;
    background: #fff;
    right: 12%; bottom: 15%;
    animation-delay: 2s;
}
@keyframes float-shape {
    0% { transform: translateY(0) scale(1); }
    100% { transform: translateY(-30px) scale(1.08); }
}
.right-panel {
    flex: 1.5;
    display: flex;
    align-items: center;
    justify-content: center;
    background: transparent;
    min-width: 320px;
}
.form-container {
    max-width: 410px;
    width: 100%;
    margin: 0 auto;
    background: rgba(255,255,255,0.93);
    border-radius: 18px;
    box-shadow: 0 8px 32px 0 rgba(31,38,135,0.18);
    padding: 8px 38px 20px 38px;
    animation: slide-in-fade-in 0.8s cubic-bezier(0.4, 0, 0.2, 1) forwards;
    color: #1e3c72;
    font-family: 'Montserrat', sans-serif;
}
.form h2 {
    text-align: center;
    margin-bottom: 28px;
    font-weight: 800;
    color: #1e3c72;
    font-size: 2rem;
    letter-spacing: 1.2px;
    text-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}
.input-group {
    position: relative;
    margin-bottom: 22px;
}
.form input[type="text"],
.form input[type="email"],
.form input[type="password"] {
    padding: 13px 16px;
    border: none;
    border-radius: 10px;
    font-size: 1rem;
    width: 100%;
    background: #f4f8fb;
    color: #1e3c72;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    transition: box-shadow 0.3s, background 0.3s;
    font-family: 'Montserrat', sans-serif;
}
.form input[type="text"]::placeholder,
.form input[type="email"]::placeholder,
.form input[type="password"]::placeholder {
    color: #b0b8c9;
    font-weight: 500;
}
.form input[type="text"]:focus,
.form input[type="email"]:focus,
.form input[type="password"]:focus {
    outline: none;
    background: #e0eafc;
    box-shadow: 0 0 0 2px #00c6ff;
}
.password-group {
    display: flex;
    align-items: center;
    position: relative;
}
.toggle-password {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    width: 22px;
    height: 22px;
    background: url('data:image/svg+xml;utf8,<svg fill="%2399a" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 5c-7 0-11 7-11 7s4 7 11 7 11-7 11-7-4-7-11-7zm0 12c-2.761 0-5-2.239-5-5s2.239-5 5-5 5 2.239 5 5-2.239 5-5 5zm0-8c-1.654 0-3 1.346-3 3s1.346 3 3 3 3-1.346 3-3-1.346-3-3-3z"/></svg>') no-repeat center/contain;
    cursor: pointer;
    opacity: 0.7;
    transition: opacity 0.2s;
}
.toggle-password.show {
    background: url('data:image/svg+xml;utf8,<svg fill="%2300c6ff" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 5c-7 0-11 7-11 7s4 7 11 7 11-7 11-7-4-7-11-7zm0 12c-2.761 0-5-2.239-5-5s2.239-5 5-5 5 2.239 5 5-2.239 5-5 5zm0-8c-1.654 0-3 1.346-3 3s1.346 3 3 3 3-1.346 3-3-1.346-3-3-3z"/></svg>') no-repeat center/contain;
    opacity: 1;
}
.form button {
    padding: 9px 35px;
    background: linear-gradient(90deg, #00c6ff, #0072ff);
    color: #fff;
    border: none;
    border-radius: 12px;
    font-size: 1.15rem;
    cursor: pointer;
    font-weight: 700;
    letter-spacing: 1.1px;
    transition: background 0.3s, transform 0.3s, box-shadow 0.3s;
    box-shadow: 0 6px 20px rgba(0, 114, 255, 0.13);
    margin-top: 8px;
}
.form button:hover {
    background: linear-gradient(90deg, #0072ff, #00c6ff);
    transform: scale(1.04);
    box-shadow: 0 10px 30px rgba(0, 114, 255, 0.18);
}
.form-footer {
    text-align: center;
    margin-top: 18px;
    font-size: 1rem;
    color: #7a8ca3;
}
.form-footer a {
    color: #00c6ff;
    text-decoration: none;
    font-weight: 700;
    transition: color 0.3s;
}
.form-footer a:hover {
    color: #0072ff;
}
.error-messages {
    background-color: #ff4d6d;
    border: none;
    color: #fff;
    padding: 12px 16px;
    border-radius: 10px;
    margin-bottom: 18px;
    font-size: 0.98rem;
    font-weight: 600;
    box-shadow: 0 4px 15px rgba(255, 77, 109, 0.13);
}
@media (max-width: 900px) {
    .split-bg { flex-direction: column; }
    .left-panel, .right-panel { min-width: 0; width: 100%; }
    .left-panel { min-height: 180px; padding: 30px 0; }
    .right-panel { min-height: 420px; }
}
@media (max-width: 600px) {
    .form-container { padding: 22px 8px 18px 8px; }
    .logo-area h1 { font-size: 1.3rem; }
    .logo-circle { width: 48px; height: 48px; font-size: 1.2rem; }
    .left-panel { min-height: 120px; }
}
@keyframes slide-in-fade-in {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
</head>
<body>
<div class="split-bg">
    <div class="left-panel">
        <div class="logo-area">
            <div class="logo-circle">TM</div>
            <h1>Task Manager</h1>
            <p>Organize your work, boost your productivity.</p>
        </div>
        <div class="animated-shapes"></div>
    </div>
    <div class="right-panel">
        <div class="form-container signup-container" role="main" aria-label="Signup form">
            <form method="POST" action="signup.php" class="form animated-form" novalidate autocomplete="off">
                <h2>Sign Up</h2>
                <?php if (!empty($errors)): ?>
                    <div class="error-messages" role="alert" aria-live="assertive">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?=htmlspecialchars($error)?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <div class="input-group">
                    <input type="text" id="username" name="username" placeholder="Username" value="<?=htmlspecialchars($_POST['username'] ?? '')?>" required autocomplete="username" autofocus />
                </div>
                <div class="input-group">
                    <input type="email" id="email" name="email" placeholder="Email address" value="<?=htmlspecialchars($_POST['email'] ?? '')?>" required autocomplete="email" />
                </div>
                <div class="input-group password-group">
                    <input type="password" id="password" name="password" placeholder="Password" required autocomplete="new-password" />
                    <span class="toggle-password" onclick="togglePassword('password', this)"></span>
                </div>
                <div class="input-group password-group">
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required autocomplete="new-password" />
                    <span class="toggle-password" onclick="togglePassword('confirm_password', this)"></span>
                </div>
                <button type="submit" aria-label="Sign Up">Sign Up</button>
                <p class="form-footer">Already have an account? <a href="login.php">Login here</a></p>
            </form>
        </div>
    </div>
</div>
<script>
function togglePassword(id, el) {
    const input = document.getElementById(id);
    if (input.type === 'password') {
        input.type = 'text';
        el.classList.add('show');
    } else {
        input.type = 'password';
        el.classList.remove('show');
    }
}
</script>
</body>
</html>
