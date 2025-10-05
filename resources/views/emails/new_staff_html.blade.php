<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a Despapela</title>
    <!-- Estilos CSS en línea para máxima compatibilidad con clientes de correo -->
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            color: #333333;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            border-radius: 8px;
            overflow: hidden;
        }
        .header {
            background-color: #006677; /* Azul Oscuro de Despapela */
            padding: 25px 30px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            font-size: 24px;
            margin: 0;
        }
        .content {
            padding: 30px;
        }
        .credentials-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background-color: #f9f9f9;
            border-radius: 6px;
            overflow: hidden;
        }
        .credentials-table th, .credentials-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eeeeee;
        }
        .credentials-table th {
            background-color: #e8e8e8;
            color: #333333;
            width: 30%;
        }
        .password-cell {
            font-family: monospace;
            font-size: 1.1em;
            color: #000000;
            font-weight: bold;
        }
        .button {
            display: inline-block;
            background-color: #99FF00; /* Verde Lima de Despapela */
            color: #000000 !important;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            text-transform: uppercase;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            transition: background-color 0.3s ease;
        }
        .footer {
            background-color: #006677; /* Azul Oscuro de Despapela */
            color: #cccccc;
            padding: 20px 30px;
            text-align: center;
            font-size: 12px;
        }
        .footer a {
            color: #ffffff;
            text-decoration: none;
        }
        .security-note {
            text-align: center;
            color: #888888;
            font-size: 13px;
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px dashed #cccccc;
        }
    </style>
</head>
<body>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f4f4f4;">
        <tr>
            <td align="center" style="padding: 20px 0;">
                <table class="container" role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                    <!-- HEADER -->
                    <tr>
                        <td class="header">
                            <h1>Bienvenido a Despapela</h1>
                        </td>
                    </tr>
                    
                    <!-- CONTENT -->
                    <tr>
                        <td class="content">
                            <p>Hola **{{ $name }}**, </p>
                            
                            <p>¡Felicidades! Has sido dado de alta como {{ $roleName }} en la empresa {{ $companyName }} en nuestra plataforma de gestión documental Despapela.</p>
                            
                            <p>A continuación, encontrarás tus credenciales de acceso:</p>

                            <!-- CREDENTIALS TABLE -->
                            <table class="credentials-table" role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <th>Email</th>
                                    <td>{{ $email }}</td>
                                </tr>
                                <tr>
                                    <th>Contraseña</th>
                                    <td class="password-cell">{{ $password }}</td>
                                </tr>
                            </table>

                            <!-- BUTTON -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="text-align: center;">
                                <tr>
                                    <td style="padding: 20px 0 30px 0;">
                                        <a href="{{ url('/login') }}" class="button">
                                            Iniciar Sesión en Despapela
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <!-- SECURITY NOTE -->
                            <div class="security-note">
                                Nota de Seguridad: Por favor, accede y "cambia tu contraseña inmediatamente" para proteger tu cuenta y la información de la empresa.
                            </div>

                            <p style="margin-top: 20px;">Gracias por ser parte del equipo de Despapela.</p>

                        </td>
                    </tr>

                    <!-- FOOTER -->
                    <tr>
                        <td class="footer">
                            &copy; {{ date('Y') }} Despapela. Todos los derechos reservados.
                            <br>
                            <span style="display: block; margin-top: 5px;">Plataforma de Gestión Documental.</span>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
