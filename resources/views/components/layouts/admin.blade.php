<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Page Title' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Barcode scanner library -->
    <script src="https://cdn.jsdelivr.net/npm/quagga@0.12.1/dist/quagga.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Inter font from Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* Theme tokens: change colors here to affect entire layout */
        :root {
            --page-bg: #f7f4ed;
            --surface: #ffffff;

            --primary: #ffffff;
            --primary-600: #a17013;
            --primary-100: #d4a63d;
            --primary-50: #f7e7bf;

            --accent: #d4a63d;

            --muted: #6b7280;
            --muted-2: #4b5563;
            --border: #e5e7eb;
            --muted-3: #d1d5db;

            --success-bg: #d1e7dd;
            --success-text: #0f5132;
            --warning-bg: #fff3cd;
            --warning-text: #664d03;
            --danger-bg: #f8d7da;
            --danger-text: #842029;

            --sidebar-bg: #050505;
            --topbar-bg: #050505;

            --text: #111827;

            --avatar-bg: #111111;
            --avatar-text: #f8fafc;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--page-bg);
            letter-spacing: -0.01em;
        }

        .theme-toggle-btn {
            border: 1px solid var(--primary-100) !important;
            color: var(--primary-100) !important;
            background: transparent !important;
            border-radius: 9999px;
            min-width: 40px;
        }

        .theme-toggle-btn:hover,
        .theme-toggle-btn:focus {
            background: rgba(212, 166, 61, 0.18) !important;
            color: #ffffff !important;
        }

        body[data-theme='dark'] {
            background-color: #0f172a;
            color: #e5e7eb;
        }

        body[data-theme='dark'] .main-content {
            background-color: #111827;
            color: #e5e7eb;
        }

        body[data-theme='dark'] .sidebar,
        body[data-theme='dark'] .top-bar {
            background-color: #020617;
        }

        body[data-theme='dark'] .card,
        body[data-theme='dark'] .widget-container,
        body[data-theme='dark'] .stat-card,
        body[data-theme='dark'] .modal-content,
        body[data-theme='dark'] .list-group-item,
        body[data-theme='dark'] .table,
        body[data-theme='dark'] .dropdown-menu {
            background-color: #1f2937 !important;
            color: #e5e7eb !important;
            border-color: #374151 !important;
        }

        body[data-theme='dark'] .dropdown-item,
        body[data-theme='dark'] .dropdown-item i,
        body[data-theme='dark'] .admin-name,
        body[data-theme='dark'] .item-details h6,
        body[data-theme='dark'] .item-details p,
        body[data-theme='dark'] .widget-header h6,
        body[data-theme='dark'] .widget-header p,
        body[data-theme='dark'] h1,
        body[data-theme='dark'] h2,
        body[data-theme='dark'] h3,
        body[data-theme='dark'] h4,
        body[data-theme='dark'] h5,
        body[data-theme='dark'] h6,
        body[data-theme='dark'] label,
        body[data-theme='dark'] p,
        body[data-theme='dark'] span,
        body[data-theme='dark'] li,
        body[data-theme='dark'] .text-dark {
            color: #e5e7eb !important;
        }

        body[data-theme='dark'] .form-control,
        body[data-theme='dark'] .form-select {
            background-color: #111827;
            color: #e5e7eb;
            border-color: #374151;
        }

        body[data-theme='dark'] .table td,
        body[data-theme='dark'] .table th,
        body[data-theme='dark'] .table-striped>tbody>tr:nth-of-type(odd)>* {
            border-color: #374151;
        }

        body[data-theme='dark'] .btn-close {
            filter: invert(1);
        }

        body[data-theme='dark'] .theme-toggle-btn {
            border-color: #facc15 !important;
            color: #facc15 !important;
        }

        /* Ensure dropdowns in table are not clipped */
        .table-responsive {
            overflow: visible !important;
        }

        .dropdown-menu {
            position: absolute !important;

            left: auto !important;
            right: 0 !important;
            top: 30% !important;
            margin-top: 0.2rem;
            min-width: 160px;
            z-index: 9999 !important;
            background: #fff !important;
            box-shadow: 0 12px 32px 0 rgba(0, 0, 0, 0.22), 0 2px 8px 0 rgba(0, 0, 0, 0.10);
            border-radius: 8px !important;
            border: 1px solid #e2e8f0 !important;
            overflow: visible !important;
            filter: none !important;
        }

        .dropdown-menu>li>.dropdown-item {
            background: #fff !important;
            z-index: 9999 !important;
        }

        .dropdown-menu>li>.dropdown-item:active,
        .dropdown-menu>li>.dropdown-item:focus {
            background: #f0f7ff !important;
            color: #222 !important;
        }

        body[data-theme='dark'] .dropdown-menu>li>.dropdown-item {
            background: transparent !important;
            color: #e5e7eb !important;
        }

        body[data-theme='dark'] .dropdown-menu>li>.dropdown-item:hover,
        body[data-theme='dark'] .dropdown-menu>li>.dropdown-item:focus,
        body[data-theme='dark'] .dropdown-menu>li>.dropdown-item:active {
            background: #111827 !important;
            color: #f9fafb !important;
        }

        body[data-theme='dark'] .dropdown-menu .dropdown-divider {
            border-top-color: #374151 !important;
            opacity: 1;
        }

        body[data-theme='dark'] .dropdown-menu .dropdown-item.text-danger,
        body[data-theme='dark'] .dropdown-menu .dropdown-item.text-danger i {
            color: #f87171 !important;
        }

        .dropdown {
            position: relative !important;
        }

        .container-fluid,
        .card,
        .modal-content {
            font-size: 13px !important;
        }

        .table th,
        .table td {
            font-size: 12px !important;
            padding: 0.35rem 0.5rem !important;
        }

        .modal-header {
            padding-top: 0.5rem !important;
            padding-bottom: 0.5rem !important;
            margin-bottom: 0.25rem !important;
        }

        .modal-footer,
        .card-header,
        .card-body,
        .row,
        .col-md-6,
        .col-md-4,
        .col-md-2,
        .col-md-12 {
            padding-top: 0.5rem !important;
            padding-bottom: 0.5rem !important;
            margin-top: 0.25rem !important;
            margin-bottom: 0.25rem !important;
        }

        .form-control,
        .form-select {
            font-size: 12px !important;
            padding: 0.35rem 0.5rem !important;
        }

        .btn,
        .btn-sm,
        .btn-primary,
        .btn-secondary,
        .btn-outline-danger,
        .btn-outline-secondary {
            font-size: 12px !important;
            padding: 0.25rem 0.5rem !important;
        }

        .badge {
            font-size: 11px !important;
            padding: 0.25em 0.5em !important;
        }

        .list-group-item,
        .dropdown-item {
            font-size: 12px !important;
            padding: 0.35rem 0.5rem !important;
        }

        .summary-card,
        .card {
            border-radius: 8px !important;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06) !important;
        }

        .icon-container {
            width: 36px !important;
            height: 36px !important;
            font-size: 1.1rem !important;
        }

        /* Sidebar styles */
        .sidebar {
            width: 265px;
            height: 100vh;
            background-color: var(--sidebar-bg);
            color: #ffffff;

            padding: 20px 0;
            position: fixed;
            transition: all 0.3s ease;
            z-index: 1040;
            overflow-y: auto;
            /* Enable vertical scrolling */
            overflow-x: hidden;
            /* Hide horizontal overflow */
        }

        /* Add custom scrollbar styling for sidebar */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: var(--sidebar-bg);
        }

        .sidebar::-webkit-scrollbar-thumb {
            background-color: #1f2937;

        }

        /* Add padding to the bottom of sidebar to ensure last items are visible */
        .sidebar .nav {
            padding-bottom: 50px;
        }

        .sidebar.collapsed {
            width: 70px;
        }

        .sidebar.collapsed .sidebar-title,
        .sidebar.collapsed .nav-link span {
            display: none;
        }

        .sidebar.collapsed .nav-link i {
            margin-right: 0;
            font-size: 1.25rem;
        }

        .sidebar.collapsed .nav-link {
            text-align: center;
            padding: 10px;
        }

        .sidebar.collapsed .nav-link.dropdown-toggle::after {
            display: none;
        }

        .sidebar-header {
            padding: 0 20px 20px;
            margin-bottom: 15px;
        }

        .sidebar-title {
            font-weight: 600;
            font-size: 1.2rem;
            color: #ffffff;
            letter-spacing: -0.02em;
        }

        /* Navigation styles */
        .nav-item {
            margin: 2px 0;
            /* Reduced from 5px to 2px */
        }

        .nav-link {
            color: #fff;
            padding: 8px 20px;
            transition: all 0.2s;
        }


        .nav-link.active {
            background: linear-gradient(135deg, #8a6114 0%, #d4a63d 100%);
            color: var(--primary);
            font-weight: 500;
        }

        .nav-link:focus,
        .nav-link:hover,
        .nav-link:focus-visible {
            color: #fff;

            outline: none;
        }

        .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
            font-size: 1.1rem;
        }

        .nav-link.dropdown-toggle::after {
            display: inline-block;
            margin-left: 0.255em;
            content: "";
            border-top: 0.3em solid;
            border-right: 0.3em solid transparent;
            border-bottom: 0;
            border-left: 0.3em solid transparent;
            float: right;
            margin-top: 8px;
        }

        #inventorySubmenu .nav-link,
        #hrSubmenu .nav-link,
        #salesSubmenu .nav-link,
        #stockSubmenu .nav-link,
        #purchaseSubmenu .nav-link {
            padding: 5px 15px;
            /* Reduced padding for all submenu links */
            font-size: 0.9rem;
        }

        /* Add these styles to further improve submenu spacing */
        .collapse .nav-item {
            margin: 1px 0;
            /* Even more compact spacing for submenu items */
        }

        .collapse .nav.flex-column {
            padding-bottom: 0;
            /* Remove extra bottom padding from nested menus */
            padding-top: 2px;
            /* Add small top padding to separate from parent */
        }

        .collapse .nav-item:last-child {
            margin-bottom: 3px;
            /* Add small space after last submenu item */
        }

        /* Disabled menu item styles */
        .nav-link.disabled {
            color: #adb5bd !important;
            cursor: not-allowed !important;
            opacity: 0.6;
            pointer-events: none;
        }

        .nav-link.disabled i {
            color: #adb5bd !important;
        }

        .nav-link.disabled:hover {
            background-color: transparent !important;
            color: #adb5bd !important;
        }

        /* Top bar styles */
        .top-bar {
            height: 60px;
            background-color: var(--topbar-bg);
            border-bottom: 1px solid var(--border);
            padding: 0 20px;
            position: fixed;
            top: 0;
            right: 0;
            left: 250px;
            z-index: 1000;
            display: flex;
            align-items: center;
            transition: left 0.3s ease;
        }

        .top-bar.collapsed {
            left: 70px;
        }

        .top-bar .title {
            color: var(--primary-100);
        }

        /* User info styles */
        .admin-info {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 5px;
            border-radius: 5px;
            transition: background-color 0.2s;
            color: #ffffff;
        }

        .admin-info:hover {
            background-color: rgba(212, 166, 61, 0.22);
        }

        .admin-avatar,
        .staff-avatar,
        .avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: var(--avatar-bg);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            letter-spacing: -0.03em;
            border: 1px solid #ffffff;
        }

        .admin-name {
            font-weight: 500;
        }

        /* Dropdown menu styles */
        .dropdown-toggle {
            cursor: pointer;
        }

        .dropdown-toggle::after {
            display: none;
        }

        .dropdown-menu {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 8px 0;
            margin-top: 10px;
            min-width: 200px;
        }

        .dropdown-item {
            padding: 8px 16px;
            display: flex;
            align-items: center;
        }

        .dropdown-item:hover {
            background-color: var(--primary-100);
        }

        .dropdown-item i {
            font-size: 1rem;
        }

        /* Main content styles */
        .main-content {
            margin-left: 260px;
            margin-top: 60px;
            padding: 20px;
            background-color: #fffaf0;
            min-height: calc(100vh - 60px);
            width: calc(100% - 250px);
            transition: all 0.3s ease;
        }



        .main-content.collapsed {
            margin-left: 70px;
            width: calc(100% - 70px);
        }

        /* Card styles */
        .stat-card,
        .widget-container {
            background: var(--surface);
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
            border: none;
            padding: 1.25rem;
            height: 100%;
        }

        .stat-value {
            font-size: 1.75rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            margin-bottom: 5px;
        }

        .stat-label {
            color: var(--muted);
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 5px;
        }

        .stat-change {
            color: var(--accent);
            font-size: 13px;
        }

        .stat-change-alert {
            color: var(--danger-text);
            font-size: 13px;
        }

        /* Tab navigation */
        .content-tabs {
            display: flex;
            border-bottom: 1px solid var(--border);
            margin-bottom: 20px;
        }

        .content-tab {
            padding: 10px 20px;
            cursor: pointer;
            font-weight: 500;
            color: var(--muted-2);
            border-bottom: 3px solid transparent;
            transition: all 0.2s;
        }

        .content-tab.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
            font-weight: 600;
        }

        .content-tab:hover:not(.active) {
            color: var(--primary);
            border-bottom-color: var(--border);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        /* Chart cards */
        .chart-card {
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            margin-bottom: 20px;
        }

        .chart-header {
            background-color: var(--surface);
            padding: 1.25rem;
            border-bottom: 1px solid var(--border);
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }

        .chart-container {
            position: relative;
            height: 300px;
            padding: 1.5rem;
        }

        /* Recent sales */
        .recent-sales-card {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border-radius: 10px;
            height: 380px;
            width: 100%;
        }

        .avatar {
            width: 40px;
            height: 40px;
            margin-right: 15px;
        }

        .amount {
            font-weight: bold;
            color: var(--accent);
        }

        /* Widget components */
        .widget-header h6 {
            font-size: 1.25rem;
            margin-bottom: 5px;
            font-weight: 600;
            color: var(--text);
            letter-spacing: -0.02em;
        }

        .widget-header p {
            font-size: 0.875rem;
            color: var(--muted-2);
            margin-bottom: 0;
        }

        /* Item rows in widgets */
        .item-row {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .item-details {
            flex-grow: 1;
            margin-right: 10px;
        }

        .item-details h6 {
            font-size: 1rem;
            margin-bottom: 3px;
            color: var(--text);
        }

        .item-details p {
            font-size: 0.875rem;
            color: var(--muted);
            margin-bottom: 0;
        }

        /* Status badges */
        .status-badge {
            padding: 0.35rem 0.65rem;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 500;
            white-space: nowrap;
        }

        .in-stock {
            background-color: #d1e7dd;
            color: var(--success-text);
        }

        .low-stock {
            background-color: var(--warning-bg);
            color: var(--warning-text);
        }

        .out-of-stock {
            background-color: var(--danger-bg);
            color: var(--danger-text);
        }

        /* Progress bars */
        .progress {
            height: 0.5rem;
            margin-top: 5px;
            background-color: var(--muted-3);
            border-radius: 0.25rem;
            overflow: hidden;
        }

        .progress-bar {
            height: 0.5rem;
        }

        /* Scrollable containers */
        .inventory-container,
        .staff-sales-container,
        .chart-scroll-container {
            scrollbar-width: thin;
            scrollbar-color: #dee2e6 #f8f9fa;
            max-height: 400px;
            overflow-y: auto;
        }

        .chart-scroll-container {
            width: 100%;
            overflow-x: auto;
        }

        .inventory-container::-webkit-scrollbar,
        .staff-sales-container::-webkit-scrollbar,
        .chart-scroll-container::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .inventory-container::-webkit-scrollbar-track,
        .staff-sales-container::-webkit-scrollbar-track,
        .chart-scroll-container::-webkit-scrollbar-track {
            background: #f8f9fa;
            border-radius: 10px;
        }

        .inventory-container::-webkit-scrollbar-thumb,
        .staff-sales-container::-webkit-scrollbar-thumb,
        .chart-scroll-container::-webkit-scrollbar-thumb {
            background-color: var(--muted-3);
            border-radius: 10px;
        }

        .modal-backdrop.show {
            z-index: 1040 !important;
        }

        .modal.show {
            z-index: 1050 !important;
        }

        .table-responsive {
            min-height: 50vh;
            overflow-y: auto;
        }

        .table th {
            border-top: none;
            font-weight: 600;
            color: #ffffff;
            background: linear-gradient(135deg, #8a6114 0%, #d4a63d 100%);
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn {
            background: linear-gradient(135deg, #d4a63d 0%, #8a6114 100%);
            color: #ffffff;
            border: none;
        }

        .modal-header {
            background: linear-gradient(135deg, #8a6114 0%, #d4a63d 100%);
            color: #ffffff;
        }




        /* Responsive styles */
        @media (max-width: 767.98px) {
            .sidebar {
                transform: translateX(-100%);
                width: 250px;
                /* Ensure sidebar takes full height but allows scrolling on mobile */
                height: 100%;
                bottom: 0;
                top: 0;
                overflow-y: auto;
            }

            .sidebar.show {
                transform: translateX(0);
                box-shadow: 2px 0 10px rgba(0, 0, 0, 0.2);
            }

            .sidebar.collapsed.show {
                width: 250px;
            }

            .top-bar {
                left: 0;
            }

            .main-content {
                margin-left: 0;
                width: 100%;
            }
        }
    </style>
    @stack('styles')
    @livewireStyles
</head>

<body>

    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header d-flex justify-content-center">
                <div class="sidebar-title">
                    <img src="{{ asset('images/logo.png') }}" alt="{{ config('shop.name') }}" width="160">
                </div>
            </div>
            <ul class="nav flex-column">

                <li>
                    <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                        <i class="bi bi-speedometer2"></i> <span>Overview</span>
                    </a>
                </li>
                {{--
                <li class="nav-item">
                    <a class="nav-link dropdown-toggle" href="#hrSubmenu" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="hrSubmenu">
                        <i class="bi bi-people"></i> <span>HR Management</span>
                    </a>
                    <div class="collapse" id="hrSubmenu">
                        <ul class="nav flex-column ms-3">
                            <li class="nav-item">
                                <a class="nav-link py-2" href="{{ route('admin.manage-admin') }}">
                <i class="bi bi-shield-lock"></i> <span>Manage Admin</span>
                </a>
                </li>
                <!-- Disabled: Manage Staff -->
                <!-- Disabled: Staff Attendance -->
                <li class="nav-item">
                    <a class="nav-link py-2 disabled" href="#">
                        <i class="bi bi-calendar-check"></i> <span>Staff Attendance</span>
                    </a>
                </li>
                <!-- Disabled: Staff Salary -->
                <li class="nav-item">
                    <a class="nav-link py-2 disabled" href="#">
                        <i class="bi bi-currency-dollar"></i> <span>Staff Salary</span>
                    </a>
                </li>
                <!-- Disabled: Loan Management -->
                <li class="nav-item">
                    <a class="nav-link py-2 disabled" href="#">
                        <i class="bi bi-credit-card"></i> <span>Loan Management</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2" href="{{ route('admin.manage-customer') }}">
                        <i class="bi bi-people"></i> <span>Manage Customer</span>
                    </a>
                </li>
            </ul>
        </div>
        </li>
        --}}
        <li class="nav-item">
            <a class="nav-link dropdown-toggle" href="#inventorySubmenu" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="inventorySubmenu">
                <i class="bi bi-basket3"></i> <span>Products</span>
            </a>
            <div class="collapse" id="inventorySubmenu">
                <ul class="nav flex-column ms-3">
                    <li class="nav-item">
                        <a class="nav-link py-2" href="{{ route('admin.Productes') }}">
                            <i class="bi bi-card-list"></i> <span>List Product</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2" href="{{ route('admin.Product-brand') }}">
                            <i class="bi bi-tags"></i> <span>Product Brand</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2" href="{{ route('admin.Product-category') }}">
                            <i class="bi bi-tags-fill"></i> <span>Product Category</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>
        <li class="nav-item">
            <a class="nav-link dropdown-toggle" href="#salesSubmenu" data-bs-toggle="collapse" role="button"
                aria-expanded="false" aria-controls="salesSubmenu">
                <i class="bi bi-cash-stack"></i> <span>Sales</span>
            </a>
            <div class="collapse" id="salesSubmenu">
                <ul class="nav flex-column ms-3">
                    {{-- <li class="nav-item">
                                <a class="nav-link py-2" href="{{ route('admin.sales-system') }}">
                    <i class="bi bi-plus-circle"></i> <span>Add Sales</span>
                    </a>
        </li> --}}
        {{-- <li class="nav-item">
                                <a class="nav-link py-2" href="{{ route('admin.sales-list') }}">
        <i class="bi bi-table"></i> <span>List Sales</span>
        </a>
        </li> --}}
        <li class="nav-item">
            <a class="nav-link py-2" href="{{ route('admin.pos-sales') }}">
                <i class="bi bi-shop"></i> <span>List Sales</span>
            </a>
        </li>
        {{-- no need him  --}}
        {{--
                            <li class="nav-item">
                                <a class="nav-link py-2" href="{{ route('admin.due-payments') }}">
        <i class="bi bi-cash-coin"></i> <span>Due Payments</span>
        </a>
        </li>
        <li class="nav-item">
            <a class="nav-link py-2" href="{{ route('admin.view-payments') }}">
                <i class="bi bi-credit-card-2-back"></i> <span>View Payments</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link py-2" href="{{ route('admin.return-product') }}">
                <i class="bi bi-collection"></i> <span>Return Product</span>
            </a>
        </li>
        --}}
        </ul>
    </div>
    </li>
    <li class="nav-item">
        <a class="nav-link dropdown-toggle" href="#stockSubmenu" data-bs-toggle="collapse" role="button"
            aria-expanded="false" aria-controls="stockSubmenu">
            <i class="bi bi-file-earmark-text"></i> <span>Quotation</span>
        </a>
        <div class="collapse" id="stockSubmenu">
            <ul class="nav flex-column ms-3">
                <li class="nav-item">
                    <a class="nav-link py-2" href="{{ route('admin.quotation-system') }}">
                        <i class="bi bi-file-plus"></i> <span>Add Quotation</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2" href="{{ route('admin.quotation-list') }}">
                        <i class="bi bi-card-list"></i> <span>List Quotation</span>
                    </a>
                </li>
                {{--
                            <li class="nav-item">
                                <a class="nav-link py-2" href="{{ route('admin.Product-stock-details') }}">
                <i class="bi bi-shield-lock"></i> <span>Product Stock</span>
                </a>
    </li>
    --}}
    </ul>
    </div>
    </li>
    <li class="nav-item">
        <a class="nav-link dropdown-toggle" href="#purchaseSubmenu" data-bs-toggle="collapse" role="button"
            aria-expanded="false" aria-controls="purchaseSubmenu">
            <i class="bi bi-truck"></i><span>Purchase</span>
        </a>
        <div class="collapse" id="purchaseSubmenu">
            <ul class="nav flex-column ms-3">
                <li class="nav-item">
                    <a class="nav-link py-2" href="{{ route('admin.purchase-order-list') }}">
                        <i class="bi bi-journal-bookmark"></i> <span>Purchase Order</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2" href="{{ route('admin.grn') }}">
                        <i class="bi bi-boxes"></i><span>GRN</span>
                    </a>
                </li>
            </ul>
        </div>
    </li>
    <li class="nav-item">
        <a class="nav-link dropdown-toggle" href="#returnSubmenu" data-bs-toggle="collapse" role="button"
            aria-expanded="false" aria-controls="returnSubmenu">
            <i class="bi bi-arrow-counterclockwise"></i> <span>Return</span>
        </a>
        <div class="collapse" id="returnSubmenu">
            <ul class="nav flex-column ms-3">
                <li class="nav-item">
                    <a class="nav-link py-2" href="{{ route('admin.return-product') }}">
                        <i class="bi bi-arrow-return-left"></i> <span>Add Customer Return</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2" href="{{ route('admin.return-list') }}">
                        <i class="bi bi-list-check"></i> <span>List Customer Return</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link py-2" href="{{ route('admin.return-supplier') }}">
                        <i class="bi bi-arrow-return-left"></i> <span>Add Supplier Return</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link py-2" href="{{ route('admin.list-supplier-return') }}">
                        <i class="bi bi-list-check"></i> <span>List Supplier Return</span>
                    </a>
                </li>

            </ul>
        </div>
    </li>
    {{-- // cheque / banks --}}
    <li class="nav-item">
        <a class="nav-link dropdown-toggle" href="#banksSubmenu" data-bs-toggle="collapse" role="button"
            aria-expanded="false" aria-controls="banksSubmenu">
            <i class="bi bi-bank"></i> <span>Cheque / Banks</span>
        </a>
        <div class="collapse" id="banksSubmenu">
            <ul class="nav flex-column ms-3">
                <li class="nav-item">
                    <a class="nav-link py-2" href="{{ route('admin.day-summary') }}">
                        <i class="bi bi-cash-stack"></i> <span>Deposit By Cash</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2" href="{{ route('admin.cheque-list') }}">
                        <i class="bi bi-card-text"></i> <span>Cheque List</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2" href="{{ route('admin.return-cheque') }}">
                        <i class="bi bi-arrow-left-right"></i> <span>Return Cheque</span>
                    </a>
                </li>
            </ul>
        </div>
    </li>
    {{--
                <li class="nav-item">
                    <a class="nav-link py-2" href="{{ route('admin.income') }}">
    <i class="bi bi-cash-stack"></i> <span>Income</span>
    </a>
    </li>
    --}}
    {{-- // Expensive  --}}
    <li class="nav-item">
        <a class="nav-link dropdown-toggle" href="#expensesSubmenu" data-bs-toggle="collapse" role="button"
            aria-expanded="false" aria-controls="expensesSubmenu">
            <i class="bi bi-wallet2"></i> <span>Expenses</span>
        </a>
        <div class="collapse" id="expensesSubmenu">
            <ul class="nav flex-column ms-3">
                <li class="nav-item">
                    <a class="nav-link py-2" href="{{ route('admin.expenses') }}">
                        <i class="bi bi-wallet2"></i> <span>List Expenses</span>
                    </a>
                </li>
            </ul>
        </div>
    </li>
    <!-- //add financing -->
    <li class="nav-item">
        <a class="nav-link dropdown-toggle" href="#paymentSubmenu" data-bs-toggle="collapse" role="button"
            aria-expanded="false" aria-controls="paymentSubmenu">
            <i class="bi bi-receipt-cutoff"></i> <span>Payment Management</span>
        </a>
        <div class="collapse" id="paymentSubmenu">
            <ul class="nav flex-column ms-3">
                <li class="nav-item">
                    <a class="nav-link py-2" href="{{ route('admin.add-customer-receipt') }}">
                        <i class="bi bi-person-plus"></i> <span>Add Customer Receipt</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2" href="{{ route('admin.list-customer-receipt') }}">
                        <i class="bi bi-people-fill"></i> <span>List Customer Receipt</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2" href="{{ route('admin.add-supplier-receipt') }}">
                        <i class="bi bi-truck-flatbed"></i> <span>Add Supplier Payment</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2" href="{{ route('admin.list-supplier-receipt') }}">
                        <i class="bi bi-clipboard-data"></i> <span>List Supplier Payment</span>
                    </a>
                </li>
            </ul>
        </div>
    </li>
    {{-- // people management --}}
    <li class="nav-item">
        <a class="nav-link dropdown-toggle" href="#peopleSubmenu" data-bs-toggle="collapse" role="button"
            aria-expanded="false" aria-controls="peopleSubmenu">
            <i class="bi bi-people-fill"></i> <span>People</span>
        </a>
        <div class="collapse" id="peopleSubmenu">
            <ul class="nav flex-column ms-3">
                <li class="nav-item">
                    <a class="nav-link py-2" href="{{ route('admin.supplier-management') }}">
                        <i class="bi bi-people"></i> <span>List Suppliers</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2" href="{{ route('admin.manage-customer') }}">
                        <i class="bi bi-person-lines-fill"></i> <span>List Customer</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2 " href="{{ route('admin.manage-staff') }}">
                        <i class="bi bi-person-badge"></i> <span>List Staff</span>
                    </a>
                </li>
            </ul>
        </div>
    </li>
    {{--<li>
                    <a class="nav-link" href="{{ route('admin.store-billing') }}" target="_blank">
    <i class="bi bi-cash"></i> <span>POS</span>
    </a>
    </li>--}}
    <li>
        <a class="nav-link" href="{{ route('admin.income') }}">
            <i class="bi bi-file-earmark-bar-graph"></i> <span>Day Summary</span>
        </a>
    </li>
    <li>
        <a class="nav-link" href="{{ route('admin.reports') }}">
            <i class="bi bi-file-earmark-bar-graph"></i> <span>Reports</span>
        </a>
    </li>
    <li>
        <a class="nav-link" href="{{ route('admin.analytics') }}">
            <i class="bi bi-bar-chart"></i> <span>Analytics</span>
        </a>
    </li>
    <li>
        <a class="nav-link" href="{{ route('admin.profit-loss') }}">
            <i class="bi bi-graph-up-arrow"></i> <span>Profit & Loss</span>
        </a>
    </li>
    <li>
        <a class="nav-link" href="{{ route('admin.profit-share') }}">
            <i class="bi bi-people-fill"></i> <span>Profit Share</span>
        </a>
    </li>
    <li>
        <a class="nav-link" href="{{ route('admin.settings') }}">
            <i class="bi bi-gear"></i> <span>Settings</span>
        </a>
    </li>
    </ul>
    </div>

    <!-- Top Navigation Bar -->
    <nav class="top-bar d-flex justify-content-between align-items-center">
        <!-- Sidebar toggle button -->
        <button id="sidebarToggler" class="btn btn-sm px-2 py-1  d-flex align-items-center" style="color:#ffffff; border-color:#ffffff;">
            <i class="bi bi-list fs-5"></i>
        </button>

        <!-- Centered Company Name (hidden on small screens) -->
        <div class="flex-grow-1 d-none d-md-flex justify-content-center">
            <h5 class="m-0 fw-bold" style="color: var(--primary-100); letter-spacing: -0.02em;">{{ config('shop.name') }}</h5>
        </div>
        @php
        use App\Models\CashInHand as CashModel;
        $cashInHand = CashModel::where('key', 'cash in hand')->value('value') ?? 0;
        @endphp

        <!-- Editable Cash in Hand Display -->
        <div class="badge  bg-opacity-10 rounded-pill shadow-sm border  border-opacity-25 d-flex align-items-center gap-2 me-2 "
            style="color:var(--primary-100);border-color:var(--primary-100); font-size: 0.9rem; cursor: pointer;"
            onclick="handlePOSClick()"
            role="button">
            <div class="d-flex align-items-center gap-1 px-2 py-1 fs-6">
                <i class="bi bi-plus-circle"></i>
                <span class="fw-semibold">POS</span>
            </div>
        </div>

        <!-- Reopen POS Button (visible only if today's POS session is closed) -->
        <div id="reopenPosBtnContainer" style="display:none;">
            <button type="button" class=" rounded-pill shadow-sm border border-opacity-25 d-flex align-items-center gap-2 me-2"
                style="font-size: 0.9rem; background-color:white; color:red; cursor: pointer;"
                onclick="showReopenPOSModal()">
                <i class="bi bi-unlock"></i>
            </button>
        </div>

        <button id="themeToggleBtn" type="button" class="btn btn-sm theme-toggle-btn d-flex align-items-center gap-1 me-2" title="Switch theme">
            <i id="themeToggleIcon" class="bi bi-moon-stars"></i>
            <span id="themeToggleText" class="d-none d-lg-inline">Dark</span>
        </button>



        <!-- Admin dropdown -->
        <div class="dropdown ms-auto">
            <div class="admin-info dropdown-toggle" id="adminDropdown" role="button" data-bs-toggle="dropdown"
                aria-expanded="false">
                @if(auth()->user()->profile_photo_path)
                <img src="{{ route('profile.photo.show', auth()->id()) }}?v={{ md5((string) auth()->user()->profile_photo_path) }}" class="admin-avatar" alt="{{ auth()->user()->name }}" style="object-fit:cover;">
                @else
                <div class="admin-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                @endif
                <div class="admin-name">{{ auth()->user()->name }}</div>
            </div>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminDropdown">
                <li>
                    <a class="dropdown-item" href="{{ route('profile.show') }}">
                        <i class="bi bi-person me-2"></i>My Profile
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('admin.settings') }}">
                        <i class="bi bi-gear me-2"></i>Settings
                    </a>
                </li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li>
                    <form method="POST" action="{{ route('logout') }}" class="mb-0">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </nav>
    <!-- Modal for Updating Cash-in-Hand -->
    <div class="modal fade" id="editCashAdminModal" tabindex="-1" aria-labelledby="editCashAdminModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-wallet2 text-warning me-2"></i> Open POS Session - {{ date('M d, Y') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form action="{{ route('admin.updateCashInHand') }}" method="POST" id="cashInHandForm">
                    @csrf
                    <div class="modal-body">
                        @php
                        // Get yesterday's closing cash
                        use App\Models\POSSession;
                        use Carbon\Carbon;

                        $yesterday = Carbon::yesterday();
                        $yesterdaySession = POSSession::where('user_id', Auth::id())
                        ->where('session_date', $yesterday)
                        ->where('status', 'closed')
                        ->first();

                        $lastClosingCash = $yesterdaySession ? $yesterdaySession->closing_cash : $cashInHand;
                        @endphp

                        <!-- Amount Input -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Opening Cash Amount *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">Rs.</span>
                                <input type="number"
                                    step="0.01"
                                    class="form-control form-control-lg"
                                    name="newCashInHand"
                                    id="openingCashInput"
                                    value="{{ number_format($lastClosingCash, 2, '.', '') }}"
                                    placeholder="Enter opening cash amount"
                                    required>
                            </div>
                            <div class="form-text">
                                Enter the cash amount to start your POS session
                            </div>
                        </div>

                        <!-- Previous Day Info -->
                        @if($yesterdaySession)
                        <div class="alert alert-info mb-3">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Yesterday's Closing Cash:</strong> Rs. {{ number_format((float) $yesterdaySession->closing_cash, 2) }}
                            <br>
                            <small class="text-muted">{{ $yesterday->format('d/m/Y') }}</small>
                        </div>
                        @endif

                        <!-- Current Cash in Hand -->
                        <div class="p-3 bg-success bg-opacity-10 rounded-3 border border-success border-opacity-25">
                            <h6 class="fw-bold text-dark mb-2">
                                <i class="bi bi-calculator text-success me-2"></i> System Cash in Hand
                            </h6>
                            <div class="d-flex justify-content-between small">
                                <span class="text-muted">Current Balance:</span>
                                <span class="fw-semibold text-success">Rs. {{ number_format($cashInHand, 2) }}</span>
                            </div>
                        </div>

                    </div>

                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success text-white">
                            <i class="bi bi-check2-circle me-1"></i> Open POS Session
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>


    <!-- Main Content -->
    <main class="main-content">
        {{ $slot }}
    </main>

    <!-- Reopen POS Confirmation Modal -->
    <div class="modal fade" id="reopenPOSModal" tabindex="-1" aria-labelledby="reopenPOSModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="reopenPOSModalLabel">
                        <i class="bi bi-unlock me-2"></i> Reopen POS Session
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to change today's POS session status from <strong>Closed</strong> to <strong>Open</strong>?<br>This will allow new POS sales for today.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="reopenPOSSession()">Yes, Reopen POS</button>
                </div>
            </div>
        </div>
    </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 from CDN (only need this one line) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Include jQuery (required by Bootstrap 4 modal) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    @livewireScripts
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Define all elements once
            const sidebarToggler = document.getElementById('sidebarToggler');
            const sidebar = document.querySelector('.sidebar');
            const topBar = document.querySelector('.top-bar');
            const mainContent = document.querySelector('.main-content');
            const themeToggleBtn = document.getElementById('themeToggleBtn');
            const themeToggleIcon = document.getElementById('themeToggleIcon');
            const themeToggleText = document.getElementById('themeToggleText');

            const themeStorageKey = 'phoenix-theme';
            const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            const darkThemeOverrideId = 'phoenix-dark-overrides';
            const darkThemeOverridesCss = `
                body[data-theme='dark'] .main-content,
                body[data-theme='dark'] .container,
                body[data-theme='dark'] .container-fluid,
                body[data-theme='dark'] .offcanvas,
                body[data-theme='dark'] .accordion-item,
                body[data-theme='dark'] .accordion-body,
                body[data-theme='dark'] .accordion-button,
                body[data-theme='dark'] .list-group-item,
                body[data-theme='dark'] .card,
                body[data-theme='dark'] .card-body,
                body[data-theme='dark'] .card-header,
                body[data-theme='dark'] .card-footer,
                body[data-theme='dark'] .modal-content,
                body[data-theme='dark'] .table,
                body[data-theme='dark'] .table-responsive,
                body[data-theme='dark'] .dropdown-menu,
                body[data-theme='dark'] .input-group-text,
                body[data-theme='dark'] .pagination .page-link,
                body[data-theme='dark'] .bg-white,
                body[data-theme='dark'] .bg-light,
                body[data-theme='dark'] .table-light,
                body[data-theme='dark'] [style*='background-color:#fff'],
                body[data-theme='dark'] [style*='background-color: #fff'],
                body[data-theme='dark'] [style*='background-color:#ffffff'],
                body[data-theme='dark'] [style*='background-color: #ffffff'],
                body[data-theme='dark'] [style*='background-color:#f8f9fa'],
                body[data-theme='dark'] [style*='background-color: #f8f9fa'],
                body[data-theme='dark'] [style*='background-color:#f7f4ed'],
                body[data-theme='dark'] [style*='background-color: #f7f4ed'],
                body[data-theme='dark'] [style*='background-color:#fffaf0'],
                body[data-theme='dark'] [style*='background-color: #fffaf0'],
                body[data-theme='dark'] [style*='background-color:#f5fdf1'],
                body[data-theme='dark'] [style*='background-color: #f5fdf1'],
                body[data-theme='dark'] [style*='background: #fff'],
                body[data-theme='dark'] [style*='background:#fff'],
                body[data-theme='dark'] [style*='background: white'],
                body[data-theme='dark'] [style*='background:white'] {
                    background: #1f2937 !important;
                    background-color: #1f2937 !important;
                    color: #e5e7eb !important;
                    border-color: #374151 !important;
                }

                body[data-theme='dark'] .table td,
                body[data-theme='dark'] .table th,
                body[data-theme='dark'] .table-striped > tbody > tr:nth-of-type(odd) > *,
                body[data-theme='dark'] hr,
                body[data-theme='dark'] .border,
                body[data-theme='dark'] [style*='border-color:#dee2e6'],
                body[data-theme='dark'] [style*='border-color: #dee2e6'] {
                    border-color: #374151 !important;
                }

                body[data-theme='dark'] h1,
                body[data-theme='dark'] h2,
                body[data-theme='dark'] h3,
                body[data-theme='dark'] h4,
                body[data-theme='dark'] h5,
                body[data-theme='dark'] h6,
                body[data-theme='dark'] p,
                body[data-theme='dark'] span,
                body[data-theme='dark'] label,
                body[data-theme='dark'] li,
                body[data-theme='dark'] td,
                body[data-theme='dark'] th,
                body[data-theme='dark'] .text-dark,
                body[data-theme='dark'] [style*='color:#212529'],
                body[data-theme='dark'] [style*='color: #212529'],
                body[data-theme='dark'] [style*='color:#495057'],
                body[data-theme='dark'] [style*='color: #495057'],
                body[data-theme='dark'] [style*='color:#3b5b0c'],
                body[data-theme='dark'] [style*='color: #3b5b0c'] {
                    color: #e5e7eb !important;
                }

                body[data-theme='dark'] .text-muted,
                body[data-theme='dark'] [style*='color:#6c757d'],
                body[data-theme='dark'] [style*='color: #6c757d'],
                body[data-theme='dark'] [style*='color:#64748b'],
                body[data-theme='dark'] [style*='color: #64748b'] {
                    color: #9ca3af !important;
                }

                body[data-theme='dark'] .form-control,
                body[data-theme='dark'] .form-select,
                body[data-theme='dark'] textarea,
                body[data-theme='dark'] input {
                    background-color: #111827 !important;
                    color: #e5e7eb !important;
                    border-color: #374151 !important;
                }

                body[data-theme='dark'] .modal-backdrop,
                body[data-theme='dark'] .modal.fade.show.d-block[style*='rgba(0,0,0,0.5)'] {
                    background-color: rgba(0, 0, 0, 0.65) !important;
                }

                body[data-theme='dark'] .swal2-popup {
                    background: #1f2937 !important;
                    color: #e5e7eb !important;
                }

                body[data-theme='dark'] .table {
                    --bs-table-bg: #1f2937;
                    --bs-table-color: #e5e7eb;
                    --bs-table-border-color: #374151;
                    --bs-table-striped-bg: #111827;
                    --bs-table-striped-color: #e5e7eb;
                    --bs-table-active-bg: #111827;
                    --bs-table-active-color: #f9fafb;
                    --bs-table-hover-bg: #273449;
                    --bs-table-hover-color: #f9fafb;
                }

                body[data-theme='dark'] .table > :not(caption) > * > * {
                    color: var(--bs-table-color) !important;
                    background-color: var(--bs-table-bg) !important;
                    border-bottom-color: #374151 !important;
                }

                /* ===== PAGE-SPECIFIC CUSTOM CLASS OVERRIDES ===== */
                body[data-theme='dark'] .stat-card,
                body[data-theme='dark'] .chart-card,
                body[data-theme='dark'] .widget-container,
                body[data-theme='dark'] .recent-sales-card,
                body[data-theme='dark'] .analytics-metric-card,
                body[data-theme='dark'] .analytics-chart-card,
                body[data-theme='dark'] .performance-card,
                body[data-theme='dark'] .chart-footer,
                body[data-theme='dark'] .pl-metric-card,
                body[data-theme='dark'] .pl-chart-card,
                body[data-theme='dark'] .filter-card,
                body[data-theme='dark'] .info-box,
                body[data-theme='dark'] .product-card,
                body[data-theme='dark'] .summary-card,
                body[data-theme='dark'] .report-card,
                body[data-theme='dark'] .metric-card,
                body[data-theme='dark'] .data-card,
                body[data-theme='dark'] .info-card {
                    background: #1f2937 !important;
                    background-color: #1f2937 !important;
                    background-image: none !important;
                    border-color: #374151 !important;
                    color: #e5e7eb !important;
                }

                body[data-theme='dark'] .chart-header {
                    background: #111827 !important;
                    background-color: #111827 !important;
                    background-image: none !important;
                    border-color: #374151 !important;
                    color: #e5e7eb !important;
                }

                body[data-theme='dark'] .metric-value,
                body[data-theme='dark'] .metric-content h6,
                body[data-theme='dark'] .metric-content p,
                body[data-theme='dark'] .month-name,
                body[data-theme='dark'] .rank-badge,
                body[data-theme='dark'] .stat-label,
                body[data-theme='dark'] .stat-value,
                body[data-theme='dark'] .item-details h6,
                body[data-theme='dark'] .item-details p,
                body[data-theme='dark'] .chart-title,
                body[data-theme='dark'] .chart-subtitle,
                body[data-theme='dark'] .pl-header h1,
                body[data-theme='dark'] .pl-header .subtitle,
                body[data-theme='dark'] .widget-header h6,
                body[data-theme='dark'] .widget-header p,
                body[data-theme='dark'] .filter-card .form-label {
                    color: #e5e7eb !important;
                }

                body[data-theme='dark'] .progress {
                    background-color: #374151 !important;
                }

                body[data-theme='dark'] .table tbody tr:hover {
                    background-color: #273449 !important;
                }

                body[data-theme='dark'] .content-tab {
                    color: #9ca3af !important;
                }

                body[data-theme='dark'] .content-tab.active {
                    color: #60a5fa !important;
                    border-bottom-color: #60a5fa !important;
                }

                body[data-theme='dark'] .avatar {
                    background-color: #374151 !important;
                    color: #9ca3af !important;
                }

                body[data-theme='dark'] .stat-change {
                    color: #6ee7b7 !important;
                }

                body[data-theme='dark'] .stat-change-alert {
                    color: #fca5a5 !important;
                }
            `;

            function syncDarkThemeOverrides(theme) {
                const existingStyle = document.getElementById(darkThemeOverrideId);

                if (theme === 'dark') {
                    if (!existingStyle) {
                        const styleEl = document.createElement('style');
                        styleEl.id = darkThemeOverrideId;
                        styleEl.textContent = darkThemeOverridesCss;
                        document.head.appendChild(styleEl);
                    }
                } else if (existingStyle) {
                    existingStyle.remove();
                }
            }

            const darkInlineOriginalAttr = 'data-dark-inline-original';
            const colorResolveCache = new Map();

            function resolveRgb(colorValue) {
                if (!colorValue) return null;

                const key = colorValue.trim().toLowerCase();
                if (colorResolveCache.has(key)) {
                    return colorResolveCache.get(key);
                }

                const probe = document.createElement('span');
                probe.style.color = key;
                probe.style.display = 'none';
                document.body.appendChild(probe);
                const resolved = window.getComputedStyle(probe).color;
                probe.remove();

                const match = resolved.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)/i);
                const rgb = match ? {
                    r: Number(match[1]),
                    g: Number(match[2]),
                    b: Number(match[3])
                } : null;

                colorResolveCache.set(key, rgb);
                return rgb;
            }

            function luminance(rgb) {
                if (!rgb) return 0;
                return (0.2126 * rgb.r + 0.7152 * rgb.g + 0.0722 * rgb.b) / 255;
            }

            function shouldDarkenBackground(value) {
                if (!value) return false;
                const normalized = value.toLowerCase();
                if (normalized.includes('gradient') || normalized.includes('transparent')) return false;
                const rgb = resolveRgb(value);
                if (!rgb) return false;
                return luminance(rgb) > 0.62;
            }

            function shouldLightenText(value) {
                if (!value) return false;
                const rgb = resolveRgb(value);
                if (!rgb) return false;
                return luminance(rgb) < 0.48;
            }

            function normalizeInlineStylesForDark() {
                const scope = document.querySelector('.main-content') || document.body;

                // 1. Fix elements with explicit inline styles
                scope.querySelectorAll('[style]').forEach(el => {
                    if (!el.hasAttribute(darkInlineOriginalAttr)) {
                        el.setAttribute(darkInlineOriginalAttr, el.getAttribute('style') || '');
                    }

                    const bgColor = el.style.getPropertyValue('background-color');
                    if (shouldDarkenBackground(bgColor)) {
                        el.style.setProperty('background-color', '#1f2937', 'important');
                    }

                    const bg = el.style.getPropertyValue('background');
                    if (bg && !bg.toLowerCase().includes('gradient') && shouldDarkenBackground(bg)) {
                        el.style.setProperty('background', '#1f2937', 'important');
                    }

                    const color = el.style.getPropertyValue('color');
                    if (shouldLightenText(color)) {
                        el.style.setProperty('color', '#e5e7eb', 'important');
                    }

                    const borderColor = el.style.getPropertyValue('border-color');
                    if (borderColor && shouldDarkenBackground(borderColor)) {
                        el.style.setProperty('border-color', '#374151', 'important');
                    }
                });

                // 2. Fix class-based backgrounds using computed styles
                // This catches custom CSS classes (.stat-card, .analytics-metric-card, etc.)
                const skipCbTags = new Set(['CANVAS', 'IMG', 'VIDEO', 'SCRIPT', 'STYLE', 'OPTION', 'HEAD', 'INPUT', 'TEXTAREA', 'SELECT', 'TABLE', 'THEAD', 'TBODY', 'TFOOT', 'TR', 'TH', 'TD']);
                const skipCbFragments = ['metric-icon', 'revenue-icon', 'expense-icon', 'salary-icon', 'profit-icon', 'sales-icon', 'due-icon', 'progress-bar'];
                scope.querySelectorAll('[class]:not([style])').forEach(el => {
                    if (skipCbTags.has(el.tagName)) return;
                    if (el.hasAttribute(darkInlineOriginalAttr)) return;
                    const classStr = el.className ? String(el.className) : '';
                    if (skipCbFragments.some(c => classStr.includes(c))) return;
                    const computed = window.getComputedStyle(el);
                    const bg = computed.backgroundColor;
                    if (!bg || bg === 'transparent' || bg === 'rgba(0, 0, 0, 0)') return;
                    const m = bg.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)/i);
                    if (!m) return;
                    const rgb = {
                        r: Number(m[1]),
                        g: Number(m[2]),
                        b: Number(m[3])
                    };
                    if (luminance(rgb) > 0.65) {
                        el.setAttribute(darkInlineOriginalAttr, el.getAttribute('style') || '');
                        el.style.setProperty('background-color', '#1f2937', 'important');
                        el.style.setProperty('background', '#1f2937', 'important');
                    }
                });
            }

            function restoreInlineStylesFromDark() {
                document.querySelectorAll('[' + darkInlineOriginalAttr + ']').forEach(el => {
                    const originalStyle = el.getAttribute(darkInlineOriginalAttr);
                    if (originalStyle === '') {
                        el.removeAttribute('style');
                    } else {
                        el.setAttribute('style', originalStyle);
                    }
                    el.removeAttribute(darkInlineOriginalAttr);
                });
            }

            function refreshThemeNormalization(theme) {
                if (theme === 'dark') {
                    normalizeInlineStylesForDark();
                } else {
                    restoreInlineStylesFromDark();
                }
            }

            function applyTheme(theme) {
                document.body.setAttribute('data-theme', theme);
                document.documentElement.setAttribute('data-theme', theme);
                syncDarkThemeOverrides(theme);
                refreshThemeNormalization(theme);

                if (themeToggleIcon) {
                    themeToggleIcon.className = theme === 'dark' ? 'bi bi-sun' : 'bi bi-moon-stars';
                }

                if (themeToggleText) {
                    themeToggleText.textContent = theme === 'dark' ? 'Light' : 'Dark';
                }
            }

            const savedTheme = localStorage.getItem(themeStorageKey);
            const initialTheme = savedTheme === 'dark' || savedTheme === 'light' ? savedTheme : (prefersDark ? 'dark' : 'light');
            applyTheme(initialTheme);

            if (themeToggleBtn) {
                themeToggleBtn.addEventListener('click', function() {
                    const currentTheme = document.body.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
                    const nextTheme = currentTheme === 'dark' ? 'light' : 'dark';
                    applyTheme(nextTheme);
                    localStorage.setItem(themeStorageKey, nextTheme);
                });
            }

            function scheduleThemeNormalization() {
                const activeTheme = document.body.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
                window.requestAnimationFrame(() => {
                    refreshThemeNormalization(activeTheme);
                });
            }

            const contentRoot = document.querySelector('.main-content') || document.body;
            const themeMutationObserver = new MutationObserver(function() {
                if (document.body.getAttribute('data-theme') === 'dark') {
                    scheduleThemeNormalization();
                }
            });
            themeMutationObserver.observe(contentRoot, {
                childList: true,
                subtree: true
            });

            document.addEventListener('livewire:load', function() {
                if (typeof Livewire !== 'undefined' && Livewire.hook) {
                    Livewire.hook('message.processed', () => {
                        scheduleThemeNormalization();
                    });
                }
            });

            // Initialize sidebar state
            function initializeSidebar() {
                // Check if sidebar state is saved in localStorage
                const sidebarCollapsed = localStorage.getItem('sidebar-collapsed') === 'true';

                if (sidebarCollapsed && window.innerWidth >= 768) {
                    sidebar.classList.add('collapsed');
                    topBar.classList.add('collapsed');
                    mainContent.classList.add('collapsed');
                }

                // On mobile, always start with sidebar hidden
                if (window.innerWidth < 768) {
                    sidebar.classList.remove('show');
                    topBar.classList.remove('collapsed');
                    mainContent.classList.remove('collapsed');
                }
            }

            // Toggle sidebar function
            function toggleSidebar(event) {
                if (event) {
                    event.stopPropagation();
                }

                if (window.innerWidth < 768) {
                    // Mobile behavior
                    sidebar.classList.toggle('show');
                } else {
                    // Desktop behavior
                    sidebar.classList.toggle('collapsed');
                    topBar.classList.toggle('collapsed');
                    mainContent.classList.toggle('collapsed');

                    // Save state to localStorage
                    localStorage.setItem('sidebar-collapsed', sidebar.classList.contains('collapsed'));
                }
            }

            // Handle sidebar interactions
            if (sidebarToggler && sidebar) {
                // Initialize sidebar
                initializeSidebar();

                // Set up single click handler
                sidebarToggler.addEventListener('click', toggleSidebar);

                // Close sidebar when clicking outside on mobile
                document.addEventListener('click', function(event) {
                    if (window.innerWidth < 768 &&
                        sidebar.classList.contains('show') &&
                        !sidebar.contains(event.target) &&
                        !sidebarToggler.contains(event.target)) {
                        sidebar.classList.remove('show');
                    }
                });

                // Handle window resize
                window.addEventListener('resize', function() {
                    if (window.innerWidth >= 768) {
                        sidebar.classList.remove('show');

                        // Restore collapsed state on desktop
                        const sidebarCollapsed = localStorage.getItem('sidebar-collapsed') === 'true';
                        if (sidebarCollapsed) {
                            sidebar.classList.add('collapsed');
                            topBar.classList.add('collapsed');
                            mainContent.classList.add('collapsed');
                        } else {
                            sidebar.classList.remove('collapsed');
                            topBar.classList.remove('collapsed');
                            mainContent.classList.remove('collapsed');
                        }
                    } else {
                        // On mobile, remove collapsed styles
                        topBar.classList.remove('collapsed');
                        mainContent.classList.remove('collapsed');
                    }
                });
            }

            // Tab Switching Functionality
            const tabs = document.querySelectorAll('.content-tab');
            if (tabs.length > 0) {
                tabs.forEach(tab => {
                    tab.addEventListener('click', function() {
                        // Remove active class from all tabs
                        tabs.forEach(t => t.classList.remove('active'));

                        // Add active class to clicked tab
                        this.classList.add('active');

                        // Hide all tab contents
                        document.querySelectorAll('.tab-content').forEach(content => {
                            content.classList.remove('active');
                        });

                        // Show the selected tab content
                        const tabId = this.getAttribute('data-tab');
                        document.getElementById(tabId).classList.add('active');
                    });
                });
            }

            // Chart Initialization with proper cleanup
            const salesChartEl = document.getElementById('salesChart');
            let salesChart = null;

            function initializeChart() {
                if (salesChartEl) {
                    // Destroy existing chart if it exists
                    if (window.salesChart) {
                        window.salesChart.destroy();
                    }

                    const ctx = salesChartEl.getContext('2d');
                    window.salesChart = new Chart(ctx, {
                        // Your existing chart configuration
                        type: 'line',
                        data: {
                            // Your chart data
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                }
            }

            // Initialize chart
            initializeChart();

            // Re-initialize chart when Livewire updates
            document.addEventListener('livewire:load', function() {
                Livewire.hook('message.processed', () => {
                    initializeChart();
                });
            });

            // General submenu activation logic
            function activateParentMenuIfSubmenuActive(parentToggleSelector, submenuSelector) {
                const parentToggle = document.querySelector(parentToggleSelector);
                const submenu = document.querySelector(submenuSelector);
                const submenuLinks = submenu ? submenu.querySelectorAll('.nav-link') : [];

                let active = false;
                const currentPath = window.location.pathname;

                submenuLinks.forEach(link => {
                    const href = link.getAttribute('href');
                    if (href && href !== '#') {
                        const hrefPath = href.replace(/^(https?:\/\/[^\/]+)/, '').split('?')[0];
                        const linkIsActive = currentPath === hrefPath ||
                            currentPath.endsWith(hrefPath) ||
                            currentPath.includes(hrefPath);

                        if (linkIsActive) {
                            link.classList.add('active');
                            active = true;
                        }
                    }
                });

                if (active && parentToggle && submenu) {
                    // Remove active from all main nav links
                    document.querySelectorAll('.sidebar > .nav > .nav-item > .nav-link:not(.dropdown-toggle)').forEach(link => {
                        link.classList.remove('active');
                    });
                    parentToggle.classList.add('active');
                    parentToggle.setAttribute('aria-expanded', 'true');
                    submenu.classList.add('show');
                }
            }

            // Initialize both HR and Inventory submenus
            activateParentMenuIfSubmenuActive('a[href="#hrSubmenu"]', '#hrSubmenu');
            activateParentMenuIfSubmenuActive('a[href="#inventorySubmenu"]', '#inventorySubmenu');
            activateParentMenuIfSubmenuActive('a[href="#salesSubmenu"]', '#salesSubmenu');
            activateParentMenuIfSubmenuActive('a[href="#stockSubmenu"]', '#stockSubmenu');
            activateParentMenuIfSubmenuActive('a[href="#purchaseSubmenu"]', '#purchaseSubmenu');

            // Replace the existing submenu activation logic with this comprehensive function
            function setActiveMenuItem() {
                // Get current path
                const currentPath = window.location.pathname;

                // First clear all active states
                document.querySelectorAll('.sidebar .nav-link').forEach(link => {
                    link.classList.remove('active');
                });

                // Reset all expanded states for dropdowns
                document.querySelectorAll('.collapse').forEach(submenu => {
                    submenu.classList.remove('show');
                });
                document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
                    toggle.setAttribute('aria-expanded', 'false');
                });

                // Check for exact match first (highest priority)
                let activeFound = false;

                // First try to find exact matches
                document.querySelectorAll('.sidebar .nav-link').forEach(link => {
                    const href = link.getAttribute('href');
                    if (href && href !== '#' && !href.startsWith('#')) {
                        const hrefPath = href.replace(/^(https?:\/\/[^\/]+)/, '').split('?')[0];

                        // Exact match gets priority
                        if (currentPath === hrefPath) {
                            link.classList.add('active');
                            activeFound = true;

                            // If this is a submenu link, expand its parent
                            const submenu = link.closest('.collapse');
                            if (submenu) {
                                submenu.classList.add('show');
                                const parentToggle = document.querySelector(`[href="#${submenu.id}"]`);
                                if (parentToggle) {
                                    parentToggle.classList.add('active');
                                    parentToggle.setAttribute('aria-expanded', 'true');
                                }
                            }
                        }
                    }
                });

                // If no exact match was found, try partial matches
                if (!activeFound) {
                    document.querySelectorAll('.sidebar .nav-link').forEach(link => {
                        const href = link.getAttribute('href');
                        if (href && href !== '#' && !href.startsWith('#')) {
                            const hrefPath = href.replace(/^(https?:\/\/[^\/]+)/, '').split('?')[0];

                            // Skip root path to avoid false positives
                            if (hrefPath !== '/' && currentPath.includes(hrefPath)) {
                                link.classList.add('active');

                                // If this is a submenu link, expand its parent
                                const submenu = link.closest('.collapse');
                                if (submenu) {
                                    submenu.classList.add('show');
                                    const parentToggle = document.querySelector(`[href="#${submenu.id}"]`);
                                    if (parentToggle) {
                                        parentToggle.classList.add('active');
                                        parentToggle.setAttribute('aria-expanded', 'true');
                                    }
                                }
                            }
                        }
                    });
                }
            }

            // Add this at the end of your document.addEventListener('DOMContentLoaded') function
            setActiveMenuItem();

            // Function to handle sidebar height and scrolling
            function adjustSidebarHeight() {
                const sidebar = document.querySelector('.sidebar');
                const windowHeight = window.innerHeight;

                if (sidebar) {
                    // Ensure the sidebar takes the full viewport height
                    sidebar.style.height = windowHeight + 'px';

                    // Check if content is taller than viewport
                    const sidebarContent = sidebar.querySelector('.nav.flex-column');
                    if (sidebarContent && sidebarContent.scrollHeight > windowHeight) {
                        // Add a class to indicate scrollable content
                        sidebar.classList.add('scrollable');
                    } else {
                        sidebar.classList.remove('scrollable');
                    }
                }
            }

            // Run on load and resize
            adjustSidebarHeight();
            window.addEventListener('resize', adjustSidebarHeight);
        });

        //cash in hand popup model 
        window.addEventListener('close-modal', event => {
            const modalId = event.detail.modalId;
            const modal = bootstrap.Modal.getInstance(document.getElementById(modalId));
            if (modal) modal.hide();
        });


        // Check POS session status and update UI (show/hide Reopen POS button)
        function checkPOSSessionStatus() {
            fetch("{{ route('admin.check-pos-session') }}", {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    // Show Reopen POS button if session is closed
                    const reopenBtnContainer = document.getElementById('reopenPosBtnContainer');
                    if (data.closed === true) {
                        if (reopenBtnContainer) reopenBtnContainer.style.display = '';
                    } else {
                        if (reopenBtnContainer) reopenBtnContainer.style.display = 'none';
                    }
                })
                .catch(() => {
                    // On error, hide button
                    const reopenBtnContainer = document.getElementById('reopenPosBtnContainer');
                    if (reopenBtnContainer) reopenBtnContainer.style.display = 'none';
                });
        }

        // Call on page load
        document.addEventListener('DOMContentLoaded', checkPOSSessionStatus);

        // Handle POS button click - check if closed before redirecting
        function handlePOSClick() {
            fetch("{{ route('admin.check-pos-session') }}", {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.closed === true) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Register Already Closed',
                            text: 'The POS register has already been closed for today. You cannot access the POS system again until tomorrow.',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#a17013'
                        });
                        return;
                    }
                    window.open("{{ route('admin.store-billing') }}", '_blank');
                })
                .catch(() => {
                    window.open("{{ route('admin.store-billing') }}", '_blank');
                });
        }

        // Show Reopen POS modal
        function showReopenPOSModal() {
            const modal = new bootstrap.Modal(document.getElementById('reopenPOSModal'));
            modal.show();
        }

        // Handle Reopen POS confirmation
        function reopenPOSSession() {
            // Hide modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('reopenPOSModal'));
            if (modal) modal.hide();

            // Redirect to store billing page
            // The mount() method will detect closed session and show opening cash modal
            window.location.href = "{{ route('admin.store-billing') }}";
        }

        // Update the form submission to redirect to POS after updating cash
        document.getElementById('cashInHandForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Close modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById('editCashAdminModal'));
                        modal.hide();

                        // Show success message and redirect to POS
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Cash-in-Hand updated successfully!',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            // Redirect to store billing page in same window
                            window.location.href = "{{ route('admin.store-billing') }}";
                        });
                    } else {
                        Swal.fire('Error!', data.message || 'Failed to update cash-in-hand', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error!', 'An error occurred while updating cash-in-hand', 'error');
                });
        });
    </script>
    @stack('scripts')
</body>

</html>