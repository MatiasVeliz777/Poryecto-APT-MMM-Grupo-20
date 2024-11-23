
<!DOCTYPE php>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pide tu hora</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f7f7f7;
        }

        header {
            background-image: url('Images/logo_clinica.png'); /* Placeholder image */
            background-size: cover;
            background-position: center;
            height: 250px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            position: relative;
            margin-bottom: 80px
        }

        header::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1;
        }

        header h1 {
            position: relative;
            z-index: 2;
            font-size: 3rem;
        }

        .breadcrumb {
            position: absolute;
            top: 10px;
            left: 20px;
            color: #fff;
            font-size: 1.2rem;
            z-index: 2;
        }

        .container {
            max-width: 1100px;
            margin: -50px auto 0;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        h2 {
            color: #008AC9;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        h3 {
            color: #2c3e50;
            margin-bottom: 30px;
        }

        form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        input, textarea {
            width: 100%;
            padding: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1rem;
        }

        textarea {
            grid-column: span 2;
            height: 150px;
        }

        .contact-info {
            display: flex;
            justify-content: space-around;
            text-align: left;
        }

        .contact-info div {
            margin: 0 20px;
        }

        .contact-info h4 {
            margin-bottom: 10px;
            font-size: 1.2rem;
            color: #008AC9;
        }

        .contact-info p {
            margin-bottom: 10px;
            font-size: 1rem;
            color: #34495e;
        }

        .submit-btn {
            grid-column: span 2;
            background-color: #00304A;
            color: white;
            padding: 15px;
            border: none;
            border-radius: 5px;
            font-size: 1.2rem;
            cursor: pointer;
        }

        .submit-btn:hover {
            background-color: #2ecc71;
        }
    </style>
</head>
<body>
    <header>
        <h1>Pide tu hora</h1>
    </header>

    <div class="container">
        <h2>Formulario para Pedir Hora</h2>
        <h3>Ponga Sus Datos</h3>
        <form>
            <input type="text" placeholder="Nombre" required>
            <input type="text" placeholder="Apellidos" required>
            <input type="email" placeholder="Email" required>
            <input type="text" placeholder="Rut" required>
            <textarea placeholder="Síntomas" required></textarea>
            <button type="submit" class="submit-btn">Enviar</button>
        </form>

        <div class="contact-info">
            <div>
                <h4>📞 Teléfono</h4>
                <p>+56(9)999-99-99</p>
                <p>+56(9)888-88-88</p>
            </div>
            <div>
                <h4>📧 Correos</h4>
                <p>Cesfam@gmail.com</p>
                <p>Cesfam2@gmail.com</p>
            </div>
        </div>
    </div>
</body>
</html>
