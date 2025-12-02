<!DOCTYPE html>
<html lang="es">
    <head>
        <title>SolTecnInd - Sistema de Gestión</title>
        <link rel="apple-touch-icon" sizes="180x180" href="/projectTiendaVirtual/favicon_io/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/projectTiendaVirtual/favicon_io/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/projectTiendaVirtual/favicon_io/favicon-16x16.png">
        <link rel="manifest" href="/projectTiendaVirtual/favicon_io/site.webmanifest">
        <meta charset="utf-8">
        <meta content="IE=edge,chrome=1" http-equiv="X-UA-Compatible">
        <meta content="Sistema de Gestión - SolTecnInd" name="description">
        <meta content="width=device-width, initial-scale=1" name="viewport">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
        <style>
            :root {
                --color-primary: #3b82f6;
                --color-primary-dark: #2563eb;
                --color-bg: #f5f6f8;
                --color-dark: #1e1e2f;
            }
            body {
                margin: 0px;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                width: 100vw;
                height: 100vh;
                min-height: 675px;
                background: linear-gradient(135deg, var(--color-dark) 0%, #2d2d40 100%);
                font-family: 'Inter', 'Segoe UI', sans-serif;
            }
            p {
                width: 100%;
                left: 0px;
                font-size: 18px;
                font-family: 'Inter', 'Segoe UI', sans-serif;
                font-weight: 400;
                letter-spacing: 0px;
                text-align: center;
                vertical-align: top;
                max-width: 550px;
                color: #e5e7eb;
                margin: 0px;
                line-height: 1.6;
            }
            a {
                color: var(--color-primary);
                text-decoration: none;
                transition: all 0.3s;
            }
            a:hover {
                cursor: pointer;
                color: var(--color-primary-dark);
                text-decoration: underline;
            }
            h1 {
                font-family: 'Inter', 'Segoe UI', sans-serif;
                font-size: 48px;
                font-weight: 700;
                letter-spacing: -1px;
                text-align: center;
                margin: 24px 0;
                color: white;
            }
            .content {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                width: 100%;
                height: 100%;
                position: relative;
                z-index: 1;
            }
            .content::before {
                content: '';
                position: absolute;
                width: 150%;
                height: 150%;
                background: radial-gradient(circle, rgba(59, 130, 246, 0.1) 0%, transparent 70%);
                animation: pulse 15s ease-in-out infinite;
                z-index: -1;
            }
            @keyframes pulse {
                0%, 100% { transform: scale(1); }
                50% { transform: scale(1.1); }
            }
            .brand-icon {
                font-size: 80px;
                color: var(--color-primary);
                margin-bottom: 24px;
            }
            .btn-primary {
                background: var(--color-primary);
                color: white;
                padding: 16px 48px;
                border-radius: 12px;
                font-size: 18px;
                font-weight: 600;
                border: none;
                cursor: pointer;
                transition: all 0.3s;
                display: inline-flex;
                align-items: center;
                gap: 12px;
                margin-top: 32px;
                text-decoration: none;
            }
            .btn-primary:hover {
                background: var(--color-primary-dark);
                transform: translateY(-2px);
                box-shadow: 0 8px 24px rgba(59, 130, 246, 0.4);
            }
            @media screen and (max-width: 580px) and (min-width: 0px) {
                h1, p, .link-container {
                    width: 80%;
                }
            }
            @media screen and (min-width: 650px) and (min-height: 0px) and (max-height: 750px) {
                .link-container {
                    margin-top: 12px;
                }
                h1 {
                    margin-top: 0px;
                    margin-bottom: 0px;
                }
            }
        </style>
    </head>
    <body>
        <div class="content">
            <i class="fas fa-layer-group brand-icon"></i>
            <h1>SolTecnInd</h1>
            <p>Sistema de Gestión Empresarial</p>
            <a href="/projectTiendaVirtual/index.php" class="btn-primary">
                <i class="fas fa-sign-in-alt"></i>
                Acceder al Sistema
            </a>
        </div>
    </body>
</html>