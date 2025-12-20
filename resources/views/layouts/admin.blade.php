<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Admin Panel')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body { background: #f3f4f6; }
        .sidebar {
            width: 260px;
            min-height: 100vh;
            background: #0f172a;
            color: #e5e7eb;
        }
        .sidebar .brand {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #1f2937;
        }
        .sidebar .nav-link {
            color: #9ca3af;
            padding: .6rem 1.25rem;
            font-size: .95rem;
        }
        .sidebar .nav-link.active,
        .sidebar .nav-link:hover {
            background: #111827;
            color: #f9fafb;
        }
        .sidebar .menu-title {
            font-size: .75rem;
            text-transform: uppercase;
            padding: .75rem 1.25rem .25rem;
            color: #6b7280;
        }
    </style>
</head>
<body>
<div class="d-flex">
    {{-- SIDEBAR --}}
    <aside class="sidebar">
        <div class="brand">
            <div class="fw-bold">POS Kedai Kribo</div>
            <small>Admin Panel</small><br>
            @auth
                <small class="text-success"><i class="bi bi-person"></i> {{ auth()->user()->name }}</small>
            @endauth
        </div>

        <div class="menu-title">Dashboard</div>
        <a href="{{ route('admin.dashboard') }}"
           class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2 me-2"></i> Dashboard Admin
        </a>

        <div class="menu-title">Operasional Bisnis</div>
        <a href="{{ route('admin.menu.index') }}"
           class="nav-link {{ request()->is('admin/menu*') ? 'active' : '' }}">
            <i class="bi bi-egg-fried me-2"></i> Manajemen Menu
        </a>
        <a href="{{ route('admin.ingredients.index') }}"
           class="nav-link {{ request()->is('admin/ingredients*') ? 'active' : '' }}">
            <i class="bi bi-bag-check me-2"></i> Bahan Baku
        </a>
        
    </aside>

    {{-- KONTEN --}}
    <main class="flex-grow-1 p-4">
        @yield('content')
    </main>
</div>

@yield('scripts')
</body>
</html>
