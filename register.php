<?php
include 'db_connect.php';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $role = trim($_POST['role']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (!empty($fullname) && !empty($email) && !empty($role) && !empty($password) && !empty($confirm_password)) {
        if ($password === $confirm_password) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $query = "INSERT INTO users (fullname, email, role, password) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssss", $fullname, $email, $role, $hashed_password);

            if ($stmt->execute()) {
                $success = "Registration successful! <a href='login.php'>Login here</a>";
            } else {
                $error = "Error registering user. Try again.";
            }
            $stmt->close();
        } else {
            $error = "Passwords do not match!";
        }
    } else {
        $error = "All fields are required!";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        body {
            background: url('background.png') no-repeat center center fixed;
            background-size: cover;
            background-color: #f4f4f4;
            display: flex;
            flex-direction: column;
            align-items: center;
            height: 100vh;
        }
        .navbar {
            width: 100%;
            background-color:rgba(128, 135, 165, 0.97);
            padding: 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .logo {
            height: 40px;
        }
        .nav-links {
            flex-grow: 1;
            display: flex;
            justify-content: center;
        }
        .nav-links div {
            background: rgb(0, 218, 0);
            padding: 8px 15px;
            border-radius: 5px;
        }
        .nav-links a {
            color: white;
            text-decoration: none;
            font-size: 18px;
            font-weight: bold;
        }
        .nav-links a:hover {
            text-decoration: underline;
        }
        .form-container {
            background-color: white;
            padding: 20px;
            width: 350px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            text-align: center;
            margin-top: 50px;
        }
        .form-container h2 {
            margin-bottom: 20px;
        }
        .form-container label {
            display: block;
            text-align: left;
            font-weight: bold;
            margin-top: 10px;
        }
        .form-container input, 
        .form-container select {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .form-container button {
            margin-top: 15px;
            width: 100%;
            background-color: #007bff;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        .form-container button:hover {
            background-color: #0056b3;
        }
        .error-message {
            color: red;
            font-size: 14px;
            margin-top: 10px;
        }
        .success-message {
            color: green;
            font-size: 14px;
            margin-top: 10px;
        }
        .login-link {
            margin-top: 15px;
            font-size: 14px;
        }
        .login-link a {
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="navbar">
        <img src="logo.png" alt="Logo" class="logo">
        <div class="nav-links">
            <div><a href="home.php">Home</a></div>
        </div>
    </div>

    <div class="form-container">
        <h2>Register</h2>
        <?php if (!empty($error)): ?>
            <p class="error-message"><?php echo $error; ?></p>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <p class="success-message"><?php echo $success; ?></p>
        <?php endif; ?>
        <form action="register.php" method="POST">
            <label for="fullname">Full Name:</label>
            <input type="text" id="fullname" name="fullname" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="role">Select Role:</label>
            <select id="role" name="role" required>
                <option value="">-- Select Role --</option>
                <option value="manager">Manager</option>
                <option value="store_keeper">Store Keeper</option>
                <option value="shelf_attendant">Shelf Attendant</option>
            </select>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <label for="confirm_password">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>

            <button type="submit">Register</button>
        </form>

        <p class="login-link">Already have an account? <a href="login.php">Login here</a></p>
    </div>

</body>
</html>
