<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aplikasi Data Siswa - Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 400px;
        }
        h2 {
            margin-bottom: 30px;
        }
        .btn {
            display: block;
            margin: 15px auto;
            padding: 15px 20px;
            width: 80%;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            text-decoration: none;
            box-shadow: 0 4px 8px rgba(0, 123, 255, 0.2);
            transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease;
            cursor: pointer;
        }

        .btn:hover {
            background-color: #0056b3;
            transform: scale(1.05); /* Sedikit membesar saat hover */
            box-shadow: 0 6px 12px rgba(0, 86, 179, 0.3); /* Bayangan lebih gelap */
        }

        .footer {
            margin-top: 20px;
            font-size: 13px;
            color: #777;
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>Selamat Datang di<br>Aplikasi Data Siswa</h2>
        <a href="login.php" class="btn">Login</a>
        <div class="footer">
            &copy; <?= date("Y") ?> Data Siswa App
        </div>
    </div>

</body>
</html>
