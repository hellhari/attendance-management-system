<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
        <title>Attendance Management System</title>
        <meta content="Admin Dashboard" name="description" />
        <meta content="Themesbrand" name="author" />
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        
        @include('layouts.head')

        <style>
            :root {
                --primary-blue: #0EA5E9;
                --primary-green: #22C55E;
                --bg-white: #FFFFFF;
                --text-heading: #0F172A;
                --text-normal: #334155;
                --text-secondary: #64748B;
                --brand-gradient: linear-gradient(135deg, #0EA5E9, #22C55E);
                --glass-bg: rgba(255,255,255,0.85);
                --shadow-light: 0 10px 30px rgba(0,0,0,0.06);
                --shadow-medium: 0 15px 40px rgba(15,23,42,0.06);
            }

            body, .content-page {
                font-family: 'Poppins', sans-serif !important;
                background-color: #f1f5f9 !important; 
                color: var(--text-normal);
            }
            
            /* Enforce White Sidebar and Topbar */
            .topbar .topbar-left,
            .left.side-menu,
            #sidebar-menu { 
                background: var(--bg-white) !important;
                background-color: var(--bg-white) !important;
                border-right: 1px solid #e2e8f0 !important;
            }
            
            /* Ensure Sidebar Links are Visible (Dark Grey) */
            #sidebar-menu > ul > li > a {
                color: var(--text-normal) !important;
            }
            #sidebar-menu > ul > li > a > i {
                color: var(--text-secondary) !important;
            }
            
            /* Active Links */
            #sidebar-menu > ul > li > a.active {
                color: var(--primary-blue) !important;
                background-color: transparent !important;
            }
            #sidebar-menu > ul > li > a.active > i {
                color: var(--primary-blue) !important;
            }
            
            /* Sidebar Section Titles */
            .menu-title {
                color: var(--text-secondary) !important;
            }
        </style>
    </head>
    <body>
        <div id="wrapper">
             @include('layouts.header')
             @include('layouts.sidebar')
             <div class="content-page">  
                <div class="content">
                    <div class="container-fluid">
                       @include('layouts.settings')
                       @yield('content')
                    </div> 
                </div> 
            </div> 
            @include('layouts.footer')  
            @include('layouts.footer-script')  
        </div> 
        @include('includes.flash')
    </body>
</html>