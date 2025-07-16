<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HorseTrader Installation</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .install-container {
            background: white;
            border-radius: 15px;
            padding: 40px;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }
        .install-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .install-header h1 {
            color: #2c3e50;
            margin-bottom: 10px;
        }
        .install-header p {
            color: #7f8c8d;
        }
        .requirement {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 8px;
            background: #f8f9fa;
        }
        .requirement.pass {
            background: #d4edda;
            border-left: 4px solid #28a745;
        }
        .requirement.fail {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
        }
        .status {
            font-weight: bold;
        }
        .status.pass {
            color: #28a745;
        }
        .status.fail {
            color: #dc3545;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            text-align: center;
            cursor: pointer;
            border: none;
            transition: all 0.3s ease;
            font-size: 16px;
            width: 100%;
        }
        .btn-primary {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #2980b9 0%, #1f5f7a 100%);
        }
        .btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
        }
        .install-steps {
            margin-top: 30px;
        }
        .step {
            margin-bottom: 20px;
            padding: 20px;
            border-radius: 8px;
            background: #f8f9fa;
        }
        .step h3 {
            color: #2c3e50;
            margin-bottom: 10px;
        }
        .code-block {
            background: #2c3e50;
            color: white;
            padding: 15px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            margin: 10px 0;
            overflow-x: auto;
        }
    </style>
</head>
<body>

<div class="install-container">
    <div class="install-header">
        <h1>🐎 HorseTrader Installation</h1>
        <p>Welcome! Let's get your horse trading platform set up.</p>
    </div>

    <div class="requirements-check">
        <h2>System Requirements</h2>
        
        <?php
        $requirements = [
            'PHP Version' => [
                'check' => version_compare(PHP_VERSION, '7.4.0', '>='),
                'current' => PHP_VERSION,
                'required' => '7.4.0+'
            ],
            'MySQL Extension' => [
                'check' => extension_loaded('mysqli'),
                'current' => extension_loaded('mysqli') ? 'Available' : 'Not Available',
                'required' => 'Required'
            ],
            'File Upload' => [
                'check' => ini_get('file_uploads'),
                'current' => ini_get('file_uploads') ? 'Enabled' : 'Disabled',
                'required' => 'Required'
            ],
            'Uploads Directory' => [
                'check' => is_writable('uploads/'),
                'current' => is_writable('uploads/') ? 'Writable' : 'Not Writable',
                'required' => 'Must be writable'
            ]
        ];
        
        $all_pass = true;
        foreach ($requirements as $name => $req) {
            $status = $req['check'] ? 'pass' : 'fail';
            if (!$req['check']) $all_pass = false;
            echo "<div class='requirement $status'>";
            echo "<span><strong>$name:</strong> {$req['current']} (Required: {$req['required']})</span>";
            echo "<span class='status $status'>" . ($req['check'] ? '✓ PASS' : '✗ FAIL') . "</span>";
            echo "</div>";
        }
        ?>
    </div>

    <?php if ($all_pass): ?>
        <div class="install-steps">
            <h2>Installation Steps</h2>
            
            <div class="step">
                <h3>1. Database Setup</h3>
                <p>Create a MySQL database and import the schema:</p>
                <div class="code-block">mysql -u your_username -p your_database < database.sql</div>
            </div>
            
            <div class="step">
                <h3>2. Configure Database Connection</h3>
                <p>Update the database credentials in <code>includes/db.php</code>:</p>
                <div class="code-block">$conn = mysqli_connect("localhost", "your_username", "your_password", "your_database");</div>
            </div>
            
            <div class="step">
                <h3>3. Test Your Installation</h3>
                <p>Once configured, test your installation:</p>
                <a href="index.php" class="btn btn-primary">Visit Your Website</a>
            </div>
        </div>
        
        <div style="margin-top: 30px; padding: 20px; background: #e3f2fd; border-radius: 8px;">
            <h3>🎉 Ready to Go!</h3>
            <p><strong>Default Login Credentials:</strong></p>
            <ul>
                <li>Email: john@example.com | Password: password</li>
                <li>Email: mary@example.com | Password: password</li>
            </ul>
            <p>Remember to change these passwords in production!</p>
        </div>
        
    <?php else: ?>
        <div style="margin-top: 30px; padding: 20px; background: #f8d7da; border-radius: 8px; text-align: center;">
            <h3>⚠️ Requirements Not Met</h3>
            <p>Please fix the failed requirements above before continuing with the installation.</p>
            <button class="btn btn-primary" disabled>Cannot Proceed</button>
        </div>
    <?php endif; ?>

    <div style="margin-top: 30px; text-align: center; color: #7f8c8d;">
        <p>Need help? Check the <a href="README.md" style="color: #3498db;">README.md</a> file for detailed instructions.</p>
    </div>
</div>

</body>
</html> 