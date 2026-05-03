<!DOCTYPE html>
<html lang="en"> 
<head>
    <title>RiseTruck</title>
    
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Portal - Bootstrap 5 Admin Dashboard Template For Developers">
    <meta name="author" content="Xiaoying Riley at 3rd Wave Media">    
    <link rel="shortcut icon" href="favicon.ico"> 
    
    <script defer src="{{ asset('assets/plugins/fontawesome/js/all.min.js') }}"></script>
    <link id="theme-style" rel="stylesheet" href="{{ asset('assets/css/portal.css') }}">

    <style>
        :root {
            --rt-primary: hsl(194, 100%, 46%);
            --rt-secondary: hsl(189, 100%, 23%);
            --rt-muted: hsl(186, 67%, 94%);
            --rt-muted-fg: hsl(189, 100%, 23%);
            --rt-border: hsl(186, 67%, 88%);
        }

        /* SIDEBAR */
        .app-sidepanel, .sidepanel-inner {
            background: #ffffff !important;
            border-right: 1px solid var(--rt-border) !important;
        }
        .app-branding {
            background: #ffffff !important;
            border-bottom: 1px solid var(--rt-border) !important;
            padding: 16px 20px;
        }
        .app-branding .app-logo {
            color: var(--rt-secondary) !important;
            font-weight: 700;
            text-decoration: none;
        }
        .logo-text {
            color: var(--rt-secondary) !important;
            font-weight: 700;
            font-size: 1.1rem;
        }
        .app-nav-main .nav-link {
            color: var(--rt-secondary) !important;
            border-radius: 8px;
            margin: 2px 10px;
            transition: all 0.2s ease;
            font-weight: 500;
        }
        .app-nav-main .nav-link:hover {
            background: var(--rt-muted) !important;
            color: var(--rt-primary) !important;
        }
        .app-nav-main .nav-link.active {
            background: linear-gradient(135deg, var(--rt-primary) 0%, var(--rt-secondary) 100%) !important;
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(0, 194, 220, 0.3);
        }
        .submenu {
            background: var(--rt-muted) !important;
            border-radius: 8px;
            margin: 0 10px;
        }
        .submenu-link {
            color: var(--rt-secondary) !important;
            transition: all 0.2s ease;
            font-weight: 500;
        }
        .submenu-link:hover {
            color: var(--rt-primary) !important;
        }
        .app-sidepanel-footer {
            background: #ffffff !important;
            border-top: 1px solid var(--rt-border) !important;
        }
        .app-nav-footer .nav-link {
            color: var(--rt-secondary) !important;
            font-weight: 500;
        }
        .app-nav-footer .nav-link:hover {
            background: var(--rt-muted) !important;
            color: var(--rt-primary) !important;
        }
        .app-nav-footer .nav-link.active {
            background: linear-gradient(135deg, var(--rt-primary) 0%, var(--rt-secondary) 100%) !important;
            color: #ffffff !important;
        }
        .nav-icon svg { fill: currentColor; }
        .submenu-arrow svg { fill: var(--rt-secondary) !important; }

        /* TOPBAR */
        .app-header {
            background: #ffffff !important;
            border-bottom: 1px solid var(--rt-border) !important;
            box-shadow: 0 2px 8px rgba(0, 174, 199, 0.08) !important;
        }

        /* CONTENT BACKGROUND */
        .app-wrapper {
            background: hsl(210, 29%, 97%) !important;
        }

        /* CARDS */
        .card {
            border: 1px solid var(--rt-border) !important;
            border-radius: 12px !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05) !important;
        }
        .card:hover {
            box-shadow: 0 8px 20px rgba(0, 174, 199, 0.12) !important;
            transform: translateY(-2px);
            transition: all 0.3s ease;
        }

        /* BUTTONS */
        .btn-primary {
            background: linear-gradient(135deg, var(--rt-primary) 0%, var(--rt-secondary) 100%) !important;
            border: none !important;
            border-radius: 8px !important;
        }
        .btn-primary:hover {
            box-shadow: 0 8px 16px rgba(0, 174, 199, 0.3) !important;
            transform: translateY(-2px);
        }

        /* TABLES */
        .table thead {
            background: var(--rt-muted) !important;
            color: var(--rt-secondary) !important;
        }
        .table tbody tr:hover {
            background: var(--rt-muted) !important;
        }

        /* BADGES */
        .badge.bg-primary {
            background: linear-gradient(135deg, var(--rt-primary) 0%, var(--rt-secondary) 100%) !important;
        }

        /* =====================
           IOS TOGGLE SWITCH
           ===================== */
        button.ios-toggle {
            position: relative !important;
            display: inline-block !important;
            width: 56px !important;
            height: 28px !important;
            min-width: 56px !important;
            max-width: 56px !important;
            border-radius: 28px !important;
            border: none !important;
            cursor: pointer !important;
            padding: 0 !important;
            font-size: 0 !important;
            line-height: 0 !important;
            vertical-align: middle !important;
            transition: background 0.3s ease !important;
            outline: none !important;
        }
        button.ios-toggle.on {
            background: #19a891 !important;
        }
        button.ios-toggle.off {
            background: #cc0000 !important;
        }
        button.ios-toggle .ios-knob {
            position: absolute !important;
            top: 3px !important;
            width: 22px !important;
            height: 22px !important;
            background: white !important;
            border-radius: 50% !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.3) !important;
            transition: left 0.3s ease !important;
            display: block !important;
        }
        button.ios-toggle.on .ios-knob {
            left: 31px !important;
        }
        button.ios-toggle.off .ios-knob {
            left: 3px !important;
        }
    </style>

</head> 

<body class="app">   	
    <header class="app-header fixed-top">	   	            
        @include('layouts.topbar')
        @include('layouts.sidebar')
    </header>
    
    <div class="app-wrapper">
        <div class="app-content pt-3 p-md-3 p-lg-4">
            <div class="container-xl">
                @yield('content')
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/plugins/popper.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.min.js') }}"></script>  
    <script src="{{ asset('assets/plugins/chart.js/chart.min.js') }}"></script> 
    <script src="{{ asset('assets/js/index-charts.js') }}"></script> 
    <script src="{{ asset('assets/js/app.js') }}"></script> 

</body>
</html>