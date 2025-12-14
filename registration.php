<?php
// ----------- STEP 1: CREATE VARIABLES TO STORE ERRORS AND SUCCESS MESSAGE ------------ //
$nameErr = $emailErr = $passErr = $confirmErr = "";
$successMsg = "";
$name = $email = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // ----------- STEP 2: VALIDATION ------------ //

    // Name validation
    if (empty($_POST["name"])) {
        $nameErr = "Name is required";
    } else {
        $name = htmlspecialchars($_POST["name"]);
    }

    // Email validation
    if (empty($_POST["email"])) {
        $emailErr = "Email is required";
    } else {
        $email = htmlspecialchars($_POST["email"]);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emailErr = "Invalid email format";
        }
    }

    // Password validation
    if (empty($_POST["password"])) {
        $passErr = "Password is required";
    } else {
        $password = $_POST["password"];

        // Check strength (minimum 6 chars + 1 special character)
        if (strlen($password) < 6 || !preg_match('/[!@#$%^&*]/', $password)) {
            $passErr = "Password must be at least 6 characters long and include a special character.";
        }
    }

    // Confirm password validation
    if (empty($_POST["confirm_password"])) {
        $confirmErr = "Confirm password is required";
    } else {
        $confirm_password = $_POST["confirm_password"];

        if ($confirm_password !== $password) {
            $confirmErr = "Passwords do not match";
        }
    }

    // If NO errors → process data
    if ($nameErr == "" && $emailErr == "" && $passErr == "" && $confirmErr == "") {

        // ----------- STEP 3: READ JSON FILE ------------ //
        $file = "users.json";

        if (!file_exists($file)) {
            $errorMsg = "JSON file not found.";
        } else {
            $jsonData = file_get_contents($file);
            $users = json_decode($jsonData, true);

            if (!is_array($users)) {
                $users = [];
            }

            // ----------- STEP 4: HASH PASSWORD ------------ //
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // ----------- STEP 5: CREATE NEW USER ------------ //
            $newUser = [
                "name" => $name,
                "email" => $email,
                "password" => $hashedPassword
            ];

            // Add user to array
            $users[] = $newUser;

            // ----------- STEP 6: WRITE BACK TO JSON ------------ //
            if (file_put_contents($file, json_encode($users, JSON_PRETTY_PRINT))) {
                $successMsg = "Registration successful!";
            } else {
                $errorMsg = "Error writing to JSON file.";
            }
        }
    }
}
?>


<!-- ----------- STEP 7: HTML FORM ------------ -->
<!DOCTYPE html>
<html>
<head>
    <title>User Registration</title>
    <style>
        body {
            text-align: center;
            font-family: Arial;
            padding: 20px;

            /* NEW — centers the form box */
            display: flex;
            justify-content: center;
            align-items: center;

            height: 100vh; /* full screen height */
        }

        .error { color: red; font-size: 14px; }
        .success { color: green; font-size: 16px; margin-bottom: 10px; }

        .form-box {
            width: 300px;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;

            /* optional — add slight shadow */
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        label { font-weight: bold; }

        input {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            margin-bottom: 10px;
        }

        button {
            width: 100%;
            padding: 10px;
            background: #4CAF50;
            color: white;
            border: none;
        }
    </style>
</head>
<body>
<div class="form-box">

    <?php if ($successMsg) echo "<p class='success'>$successMsg</p>"; ?>
    <?php if (!empty($errorMsg)) echo "<p class='error'>$errorMsg</p>"; ?>

    <h2>User Registration</h2>

    <form method="POST" action="">
        <label>Name:</label>
        <input type="text" name="name" value="<?php echo $name; ?>">
        <span class="error"><?php echo $nameErr; ?></span>

        <label>Email:</label>
        <input type="text" name="email" value="<?php echo $email; ?>">
        <span class="error"><?php echo $emailErr; ?></span>

        <label>Password:</label>
        <input type="password" name="password">
        <span class="error"><?php echo $passErr; ?></span>

        <label>Confirm Password:</label>
        <input type="password" name="confirm_password">
        <span class="error"><?php echo $confirmErr; ?></span>

        <button type="submit">Register</button>
    </form>

</div>

</body>
</html>



