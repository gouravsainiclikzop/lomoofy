<?php

use Illuminate\Support\Facades\DB;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? null;
    $mode   = $_POST['mode'] ?? 'import_only';

    if (!$action) {
        die('No action selected.');
    }

    // Disable foreign key checks
    DB::statement('SET FOREIGN_KEY_CHECKS=0');

    // Drop tables if requested
    if ($mode === 'drop_and_import') {
        $tables = DB::select('SHOW TABLES');
        $dbName = DB::getDatabaseName();
        $key = "Tables_in_{$dbName}";

        foreach ($tables as $table) {
            DB::statement("DROP TABLE IF EXISTS `{$table->$key}`");
        }
    }

    // ===== ACTION 1: SQL FILE UPLOAD =====
    if ($action === 'upload_sql') {

        if (!isset($_FILES['sql_file']) || $_FILES['sql_file']['error'] !== UPLOAD_ERR_OK) {
            die('SQL file upload failed.');
        }

        $fileName = $_FILES['sql_file']['name'];
        $tmpFile  = $_FILES['sql_file']['tmp_name'];

        if (pathinfo($fileName, PATHINFO_EXTENSION) !== 'sql') {
            die('Only .sql files are allowed.');
        }

        DB::unprepared(file_get_contents($tmpFile));
    }

    // ===== ACTION 2: RAW SQL EXECUTION =====
    if ($action === 'raw_sql') {

        $rawSql = trim($_POST['raw_sql'] ?? '');

        if ($rawSql === '') {
            die('Raw SQL is empty.');
        }

        DB::unprepared($rawSql);
    }

    // Re-enable foreign key checks
    DB::statement('SET FOREIGN_KEY_CHECKS=1');

    echo <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>Database Operation Complete</title>
</head>
<body>
    <h2>Operation completed</h2>
    <p>Action: <strong>{$action}</strong></p>
    <p>Mode: <strong>{$mode}</strong></p>
</body>
</html>
HTML;

    exit;
}

// GET request – UI
echo <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>Danger Zone</title>
</head>
<body>
    <h1>Database Utility</h1>
    <p>This tool can destroy or mutate your database. Act accordingly.</p>

    <form method="POST" enctype="multipart/form-data">

        <h3>Execution Mode</h3>
        <label>
            <input type="radio" name="mode" value="import_only" checked>
            Import / Execute only (no table drop)
        </label><br>

        <label>
            <input type="radio" name="mode" value="drop_and_import">
            Drop ALL tables first
        </label>

        <hr>

        <h3>Option 1: Upload SQL File</h3>
        <input type="file" name="sql_file" accept=".sql">
        <br>
        <button type="submit" name="action" value="upload_sql">
            Execute Uploaded SQL
        </button>

        <hr>

        <h3>Option 2: Run Raw SQL</h3>
        <textarea name="raw_sql" rows="10" cols="100"
            placeholder="DROP TABLE users; CREATE TABLE users (...);"></textarea>
        <br>
        <button type="submit" name="action" value="raw_sql">
            Execute Raw SQL
        </button>

    </form>
</body>
</html>
HTML;
