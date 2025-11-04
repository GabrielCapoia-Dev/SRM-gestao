<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema SRM - Sala de Recursos Multifuncionais</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0a1628;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: #3a4252ff;
            border-radius: 16px;
            border: 1px solid #2a3544;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
            max-width: 500px;
            width: 100%;
            padding: 50px 40px;
            text-align: center;
        }

        .logo {
            max-width: 250px;
            width: 100%;
            height: auto;
            margin-bottom: 30px;
        }

        .system-title {
            color: #22c55e;
            font-size: 1.5rem;
            margin-bottom: 15px;
            font-weight: 600;
        }

        h1 {
            color: #ffffff;
            font-size: 2rem;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .subtitle {
            color: #e9f0f0;
            font-size: 1.1rem;
            margin-bottom: 10px;
            font-weight: 500;
        }

        .description {
            color: #c0d4d4;
            font-size: 0.95rem;
            margin-bottom: 40px;
            line-height: 1.6;
        }

        .btn-access {
            display: inline-block;
            background: #22c55e;
            color: white;
            text-decoration: none;
            padding: 16px 50px;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(34, 197, 94, 0.3);
        }

        .btn-access:hover {
            background: #16a34a;
            box-shadow: 0 4px 12px rgba(34, 197, 94, 0.4);
        }

        .btn-access:active {
            transform: scale(0.98);
        }

        .footer {
            margin-top: 40px;
            color: #929292;
            font-size: 0.85rem;
        }

        @media (max-width: 768px) {
            .container {
                padding: 40px 30px;
            }

            h1 {
                font-size: 1.6rem;
            }

            .system-title {
                font-size: 1.3rem;
            }

            .subtitle {
                font-size: 1rem;
            }

            .description {
                font-size: 0.9rem;
            }

            .btn-access {
                padding: 14px 40px;
                font-size: 1rem;
            }

            .logo {
                max-width: 200px;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 30px 25px;
            }

            h1 {
                font-size: 1.4rem;
            }

            .system-title {
                font-size: 1.2rem;
            }

            .logo {
                max-width: 180px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <img src="/images/brasao-umuarama.png" alt="Brasão Umuarama" class="logo">
        
        <div class="system-title">SRM-Gestão</div>
        <h1>Bem-vindo</h1>
        <p class="subtitle">Sala de Recursos Multifuncionais</p>
        <p class="description">
            Sistema de coleta e acompanhamento de dados para educação especial e inclusiva, 
            voltado ao desenvolvimento de crianças com necessidades especiais.
        </p>
        
        <a href="/admin/login" class="btn-access">
            Acessar o Sistema
        </a>
        
        <div class="footer">
            Prefeitura Municipal de Umuarama
        </div>
    </div>
</body>
</html>