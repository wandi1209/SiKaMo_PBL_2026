<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SiKaMo — Sistem Kasir Mobile untuk UMKM Modern</title>
    <meta name="description" content="SiKaMo adalah platform POS berbasis cloud untuk UMKM. Kelola kasir, inventaris, dan analitik penjualan dalam satu dashboard yang powerful.">
    
    <!-- PWA Meta Tags -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="color-scheme" content="light">
    <meta name="theme-color" content="#ffffff">
    <meta name="theme-color" content="#ffffff" media="(prefers-color-scheme: dark)">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <link rel="apple-touch-icon" href="{{ asset('assets/logo.svg') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Grid pattern subtle */
        .hero-grid {
            background-image:
                linear-gradient(rgba(21,128,61,0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(21,128,61,0.05) 1px, transparent 1px);
            background-size: 56px 56px;
        }
        /* Orb blur blob */
        .orb {
            border-radius: 50%;
            filter: blur(90px);
            position: absolute;
            pointer-events: none;
        }
        /* Card hover */
        .feature-card {
            transition: transform 0.3s cubic-bezier(.4,0,.2,1), box-shadow 0.3s cubic-bezier(.4,0,.2,1);
        }
        .feature-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 60px -10px rgba(21,128,61,0.15);
        }
        /* Shimmer button */
        .btn-primary {
            background: linear-gradient(135deg, #15803d 0%, #22c55e 50%, #65a30d 100%);
            background-size: 200% auto;
            animation: shimmer 3s linear infinite;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn-primary:hover {
            transform: translateY(-2px) scale(1.02);
            box-shadow: 0 12px 40px rgba(21,128,61,0.35);
        }
        @keyframes shimmer {
            0% { background-position: 0% center; }
            100% { background-position: 200% center; }
        }
        /* Navbar scroll */
        header.scrolled {
            background: rgba(255,255,255,0.97) !important;
            box-shadow: 0 1px 24px rgba(0,0,0,0.07);
            border-color: rgba(226,232,240,0.9) !important;
        }
        /* Mobile menu */
        #mobile-menu { max-height: 0; overflow: hidden; transition: max-height 0.35s cubic-bezier(.4,0,.2,1); }
        #mobile-menu.open { max-height: 320px; }
        /* Testimonial */
        .testi-card { transition: transform 0.3s, box-shadow 0.3s; }
        .testi-card:hover { transform: translateY(-4px); box-shadow: 0 16px 48px rgba(0,0,0,0.08); }
        /* FAQ arrow */
        .faq-arrow { transition: transform 0.3s ease; }
        .faq-arrow.open { transform: rotate(180deg); }
        /* Counter */
        .stat-num { font-variant-numeric: tabular-nums; }
        /* Step connector */
        .step-line::after {
            content: '';
            display: block;
            position: absolute;
            top: 50%;
            left: calc(100% + 12px);
            right: calc(-100% + 12px);
            height: 2px;
            background: linear-gradient(90deg, #bbf7d0, #d1fae5);
            z-index: 0;
        }
        /* Marquee Animation */
        .marquee-container {
            overflow: hidden;
            white-space: nowrap;
            position: relative;
            display: flex;
        }
        .marquee-content {
            display: flex;
            gap: 4rem;
            animation: marquee 30s linear infinite;
        }
        @keyframes marquee {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
        .marquee-container:hover .marquee-content {
            animation-play-state: paused;
        }
        
        /* Typed JS cursor color */
        .typed-cursor {
            color: #22c55e;
        }

        /* Prevent Horizontal Scroll */
        html, body {
            max-width: 100%;
            overflow-x: hidden !important;
            position: relative;
            margin: 0;
            padding: 0;
            width: 100%;
        }
    </style>
</head>
<body class="font-sans antialiased bg-white text-slate-800 overflow-x-hidden">

{{-- Scroll Progress Bar --}}
<div id="scroll-progress" class="fixed top-0 left-0 h-1 bg-gradient-to-r from-green-400 via-emerald-500 to-lime-400 z-[100] w-0 shadow-[0_0_10px_rgba(34,197,94,0.5)]"></div>

{{-- ══════════════ NAVBAR ══════════════ --}}
<header id="navbar" class="fixed top-0 inset-x-0 z-50 transition-all duration-300 bg-white/80 backdrop-blur-lg border-b border-slate-200/60">
    <div class="container mx-auto px-6 h-[68px] flex items-center justify-between gap-6">

        {{-- Logo --}}
        <a href="/" class="flex items-center gap-2.5 group shrink-0">
            <img src="{{ asset('assets/logo.svg') }}" alt="SiKaMo" class="h-9 w-auto group-hover:scale-105 transition-transform duration-300">
            <span class="font-outfit font-black text-2xl text-slate-900">SiKaMo<span class="text-green-600">.</span></span>
        </a>

        {{-- Desktop Nav --}}
        <nav class="hidden md:flex items-center gap-7 font-inter text-sm font-medium">
            <a href="#fitur" class="text-slate-500 hover:text-green-700 transition-colors">Fitur</a>
            <a href="#cara-kerja" class="text-slate-500 hover:text-green-700 transition-colors">Cara Kerja</a>
            <a href="#testimoni" class="text-slate-500 hover:text-green-700 transition-colors">Testimoni</a>
            <a href="#faq" class="text-slate-500 hover:text-green-700 transition-colors">FAQ</a>
        </nav>

        <div class="hidden md:flex items-center gap-3">
            @auth
                @php $dashboardUrl = auth()->user()->role === 'admin' ? '/admin' : '/merchant'; @endphp
                <a href="{{ $dashboardUrl }}" class="btn-primary px-5 py-2.5 rounded-xl text-white text-sm font-bold inline-flex items-center gap-2">
                    Dashboard
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </a>
            @else
                <a href="/login" class="px-5 py-2.5 rounded-xl text-slate-600 text-sm font-semibold hover:bg-slate-100 transition-colors">Masuk</a>
                <a href="/login" class="btn-primary px-5 py-2.5 rounded-xl text-white text-sm font-bold">Coba Gratis</a>
            @endauth
        </div>

        {{-- Mobile toggle --}}
        <button id="mobile-toggle" class="md:hidden p-2 rounded-lg text-slate-600 hover:bg-slate-100 transition-colors">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
    </div>

    {{-- Mobile Menu --}}
    <div id="mobile-menu" class="md:hidden bg-white border-t border-slate-100">
        <div class="container mx-auto px-6 py-5 flex flex-col gap-4">
            <a href="#fitur" class="text-slate-600 hover:text-green-700 transition-colors mobile-link font-medium py-1">Fitur</a>
            <a href="#cara-kerja" class="text-slate-600 hover:text-green-700 transition-colors mobile-link font-medium py-1">Cara Kerja</a>
            <a href="#testimoni" class="text-slate-600 hover:text-green-700 transition-colors mobile-link font-medium py-1">Testimoni</a>
            <a href="#faq" class="text-slate-600 hover:text-green-700 transition-colors mobile-link font-medium py-1">FAQ</a>
            @auth
                <a href="{{ auth()->user()->role === 'admin' ? '/admin' : '/merchant' }}" class="btn-primary px-5 py-3 rounded-xl text-white text-center font-bold mt-2">Buka Dashboard</a>
            @else
                <a href="/login" class="btn-primary px-5 py-3 rounded-xl text-white text-center font-bold mt-2">Mulai Gratis Sekarang</a>
            @endauth
        </div>
    </div>
</header>

{{-- ══════════════ HERO ══════════════ --}}
<section class="relative min-h-[95vh] flex items-center overflow-hidden bg-gradient-to-br from-green-50 via-white to-emerald-50/60 hero-grid pt-[68px]">
    {{-- Orbs --}}
    <div class="orb w-[500px] h-[500px] bg-green-300/30 -top-32 -right-32 animate-[float_12s_ease-in-out_infinite]"></div>
    <div class="orb w-[350px] h-[350px] bg-emerald-200/40 bottom-0 -left-24 animate-[float_15s_ease-in-out_3s_infinite]"></div>
    <div class="orb w-[200px] h-[200px] bg-lime-200/50 top-1/3 left-1/4 animate-[float_10s_ease-in-out_1s_infinite]"></div>

    <div class="container mx-auto px-6 py-20 relative z-10">
        <div class="max-w-5xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-14 items-center">

                {{-- Left: Copy --}}
                <div>
                    {{-- Badge --}}
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-green-100 border border-green-200 text-green-800 text-xs font-bold mb-7 animate-fade-in-up">
                        <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                        Platform POS #1 untuk UMKM Indonesia
                    </div>

                    <h1 class="text-3xl sm:text-4xl lg:text-6xl font-outfit font-black text-slate-900 leading-[1.15] lg:leading-[1.08] tracking-tight mb-6 animate-fade-in-up" style="animation-delay:100ms">
                        Kasir Digital
                        <br>
                        <span class="relative inline-block min-w-[180px] sm:min-w-[280px]">
                            <span class="relative z-10" style="background:linear-gradient(135deg,#15803d,#22c55e,#65a30d);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;" id="typed-text"></span>
                            <svg class="absolute -bottom-2 left-0 w-full animate-pulse opacity-70" viewBox="0 0 280 12" fill="none" xmlns="http://www.w3.org/2000/svg" style="-webkit-text-fill-color:initial;"><path d="M2 9C47 3 100 1 160 4C200 6.5 242 9 278 7" stroke="#22c55e" stroke-width="3" stroke-linecap="round"/></svg>
                        </span>
                        <br>untuk UMKM Anda.
                    </h1>

                    <p class="text-base sm:text-lg text-slate-500 leading-relaxed mb-10 max-w-lg animate-fade-in-up font-inter" style="animation-delay:200ms">
                        Sederhanakan kasir, inventaris, dan laporan omzet dalam <strong class="text-slate-700 font-semibold">satu platform cloud</strong> yang mudah digunakan oleh siapa saja.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-4 animate-fade-in-up" style="animation-delay:300ms">
                        @auth
                            @php $dashboardUrl = auth()->user()->role === 'admin' ? '/admin' : '/merchant'; @endphp
                            <a href="{{ $dashboardUrl }}" class="btn-primary magnetic-btn inline-flex items-center justify-center gap-2 px-8 py-4 rounded-2xl text-white font-bold text-base transition-transform duration-200">
                                Buka Dashboard Saya
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                            </a>
                            <button id="install-pwa-btn" class="hidden inline-flex items-center justify-center gap-2 px-8 py-4 rounded-2xl bg-white border border-slate-200 text-slate-700 font-semibold hover:bg-slate-50 hover:border-slate-300 transition-all text-base shadow-sm">
                                <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                Install Aplikasi
                            </button>
                        @else
                            <a href="/login" class="btn-primary magnetic-btn inline-flex items-center justify-center gap-2 px-8 py-4 rounded-2xl text-white font-bold text-base transition-transform duration-200">
                                Coba Gratis Sekarang
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                            </a>
                            <button id="install-pwa-btn-guest" class="hidden inline-flex items-center justify-center gap-2 px-8 py-4 rounded-2xl bg-white border border-green-200 text-green-700 font-semibold hover:bg-green-50 transition-all text-base shadow-sm">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                Install App
                            </button>
                        @endauth
                    </div>

                    {{-- Trust badges --}}
                    <div class="flex items-center gap-5 mt-10 animate-fade-in-up" style="animation-delay:400ms">
                        <div class="flex -space-x-2">
                            @foreach(['🧑‍💼','👩‍🍳','🧑‍💻','👩‍💼','🧑‍🎨'] as $emoji)
                            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-green-100 to-emerald-200 border-2 border-white flex items-center justify-center text-sm">{{ $emoji }}</div>
                            @endforeach
                        </div>
                        <div>
                            <div class="flex gap-0.5 mb-0.5">
                                @for($i=0;$i<5;$i++)<svg class="w-3.5 h-3.5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>@endfor
                            </div>
                            <p class="text-xs text-slate-500"><strong class="text-slate-700">2.500+</strong> UMKM mempercayai SiKaMo</p>
                        </div>
                    </div>
                </div>

                {{-- Right: Dashboard preview --}}
                <div class="relative animate-fade-in-up hidden lg:block" style="animation-delay:400ms">
                    <div class="absolute inset-0 bg-gradient-to-r from-green-400/20 via-emerald-300/20 to-lime-300/20 rounded-3xl blur-3xl scale-105"></div>
                    <div class="relative rounded-3xl shadow-2xl border border-white/80 bg-white/60 backdrop-blur-xl p-2 tilt-element">
                        <div class="rounded-2xl overflow-hidden border border-slate-200/60 bg-white">
                            <div class="bg-slate-100 px-4 py-2.5 flex items-center gap-2">
                                <div class="flex gap-1.5">
                                    <div class="w-3 h-3 rounded-full bg-red-400"></div>
                                    <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                                    <div class="w-3 h-3 rounded-full bg-green-400"></div>
                                </div>
                                <div class="flex-1 mx-3 bg-white rounded px-3 py-1 text-xs text-slate-400 border border-slate-200 flex items-center gap-2">
                                    <svg class="w-3 h-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    app.sikamo.id/merchant
                                </div>
                            </div>
                            
                            <!-- CSS Mockup Dashboard -->
                            <div class="w-full h-[320px] bg-slate-50 flex overflow-hidden">
                                <!-- Sidebar -->
                                <div class="w-16 border-r border-slate-200/80 bg-white flex flex-col items-center py-4 gap-4 shrink-0">
                                    <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-green-600 to-emerald-700 shadow-sm flex items-center justify-center text-white font-bold text-sm">S</div>
                                    <div class="w-8 h-8 rounded-lg bg-green-50 text-green-600 flex items-center justify-center mt-2 cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                                    </div>
                                    <div class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-600 flex items-center justify-center cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </div>
                                    <div class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-600 flex items-center justify-center cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                    </div>
                                    <div class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-600 flex items-center justify-center mt-auto cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </div>
                                </div>
                                
                                <!-- Main Content -->
                                <div class="flex-1 p-5 flex flex-col gap-4">
                                    <!-- Header -->
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <div class="h-4 w-32 bg-slate-800 rounded mb-1.5 font-bold text-xs flex items-center px-2 text-white">Ringkasan Hari Ini</div>
                                            <div class="h-2.5 w-24 bg-slate-200 rounded"></div>
                                        </div>
                                        <div class="flex gap-2">
                                            <div class="w-8 h-8 rounded-full bg-slate-200 border-2 border-white shadow-sm overflow-hidden flex items-center justify-center bg-gradient-to-r from-emerald-100 to-teal-100">
                                                <span class="text-[10px] font-bold text-teal-800">WD</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Stats Row -->
                                    <div class="grid grid-cols-3 gap-3">
                                        <div class="bg-white p-3 rounded-xl border border-slate-100 shadow-sm relative overflow-hidden group">
                                            <div class="absolute right-0 top-0 w-12 h-12 bg-green-50 rounded-bl-3xl z-0"></div>
                                            <div class="relative z-10">
                                                <div class="text-slate-400 text-[10px] uppercase font-bold tracking-wider mb-1">Penjualan</div>
                                                <div class="text-slate-800 text-sm font-black mb-1">Rp 4.2M</div>
                                                <div class="flex items-center gap-1 text-[9px] text-green-600 font-bold">
                                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                                                    +12.5%
                                                </div>
                                            </div>
                                        </div>
                                        <div class="bg-white p-3 rounded-xl border border-slate-100 shadow-sm">
                                            <div class="text-slate-400 text-[10px] uppercase font-bold tracking-wider mb-1">Transaksi</div>
                                            <div class="text-slate-800 text-sm font-black mb-1">428</div>
                                            <div class="flex items-center gap-1 text-[9px] text-green-600 font-bold">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                                                +5.2%
                                            </div>
                                        </div>
                                        <div class="bg-white p-3 rounded-xl border border-slate-100 shadow-sm">
                                            <div class="text-slate-400 text-[10px] uppercase font-bold tracking-wider mb-1">Pelanggan</div>
                                            <div class="text-slate-800 text-sm font-black mb-1">112</div>
                                            <div class="flex items-center gap-1 text-[9px] text-slate-400 font-bold">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14"/></svg>
                                                0.0%
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Chart Area -->
                                    <div class="flex-1 bg-white rounded-xl border border-slate-100 shadow-sm p-4 flex flex-col justify-end">
                                        <div class="flex-1 flex items-end gap-3 px-1 pt-4">
                                            @foreach([30, 50, 40, 70, 45, 80, 60] as $h)
                                            <div class="mockup-bar w-full bg-green-50 rounded-t-md relative group transition-all duration-700 ease-out" style="height: {{ $h }}%">
                                                <div class="absolute bottom-0 w-full bg-gradient-to-t from-green-500 to-green-400 rounded-t-md opacity-80 group-hover:opacity-100 transition-all duration-300 shadow-[0_-2px_10px_rgba(34,197,94,0.3)]" style="height: 100%; animation: fade-in-up 1s ease-out forwards; animation-delay: {{ $loop->index * 100 }}ms; opacity: 0;"></div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Floating stat cards --}}
                    <div class="absolute -top-5 -left-8 bg-white rounded-2xl shadow-xl border border-slate-100 p-4 flex items-center gap-3 animate-float">
                        <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center text-green-700 shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400 font-medium">Omzet Hari Ini</p>
                            <p class="text-slate-900 font-black text-sm">Rp 4.280.000</p>
                        </div>
                    </div>

                    <div class="absolute -bottom-5 -right-8 bg-white rounded-2xl shadow-xl border border-slate-100 p-4 flex items-center gap-3 animate-float-delay">
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center text-emerald-700 shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400 font-medium">Transaksi Sukses</p>
                            <p class="text-slate-900 font-black text-sm">428 hari ini</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Stats row --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-8 sm:gap-4 mt-20 border-t border-green-100 pt-12">
                <div class="text-center" data-aos="zoom-in-up" data-aos-delay="500">
                    <div class="text-4xl font-outfit font-black text-green-700"><span class="purecounter" data-purecounter-start="0" data-purecounter-end="2500" data-purecounter-duration="2">0</span><span class="text-green-500">+</span></div>
                    <div class="text-slate-500 text-sm mt-1 font-inter">UMKM Aktif</div>
                </div>
                <div class="text-center" data-aos="zoom-in-up" data-aos-delay="700">
                    <div class="text-4xl font-outfit font-black text-green-700"><span class="purecounter" data-purecounter-start="0" data-purecounter-end="1.2" data-purecounter-decimals="1" data-purecounter-duration="2">0</span><span class="text-green-500">JT+</span></div>
                    <div class="text-slate-500 text-sm mt-1 font-inter">Transaksi/Bulan</div>
                </div>
                <div class="text-center" data-aos="zoom-in-up" data-aos-delay="900">
                    <div class="text-4xl font-outfit font-black text-green-700"><span class="purecounter" data-purecounter-start="0" data-purecounter-end="99.9" data-purecounter-decimals="1" data-purecounter-duration="2">0</span><span class="text-green-500">%</span></div>
                    <div class="text-slate-500 text-sm mt-1 font-inter">Uptime Server</div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ══════════════ LOGO CLOUD ══════════════ --}}
<section class="py-12 bg-slate-50 border-y border-slate-200/70 overflow-hidden relative">
    <div class="container mx-auto px-6">
        <p class="text-center text-xs font-bold text-slate-400 uppercase tracking-widest mb-7">Dipercaya oleh ratusan pelaku UMKM</p>
        
        <div class="marquee-container w-full max-w-5xl mx-auto">
            <!-- Fade gradients for smooth entering/exiting -->
            <div class="absolute left-0 top-0 bottom-0 w-32 bg-gradient-to-r from-slate-50 to-transparent z-10 pointer-events-none"></div>
            <div class="absolute right-0 top-0 bottom-0 w-32 bg-gradient-to-l from-slate-50 to-transparent z-10 pointer-events-none"></div>
            
            <div class="marquee-content opacity-40 hover:opacity-100 transition-opacity duration-300">
                @for($i=0; $i<3; $i++)
                <div class="flex items-center gap-16 pr-16">
                    <span class="text-2xl font-black font-outfit text-slate-700 grayscale hover:grayscale-0 transition-all cursor-default">TokoMaju</span>
                    <span class="text-2xl font-black font-outfit text-slate-700 grayscale hover:grayscale-0 transition-all cursor-default">CafeSenja</span>
                    <span class="text-2xl font-black font-outfit text-slate-700 grayscale hover:grayscale-0 transition-all cursor-default">RestoEnak</span>
                    <span class="text-2xl font-black font-outfit text-slate-700 grayscale hover:grayscale-0 transition-all cursor-default">ButikCantik</span>
                    <span class="text-2xl font-black font-outfit text-slate-700 grayscale hover:grayscale-0 transition-all cursor-default">WarbungKita</span>
                    <span class="text-2xl font-black font-outfit text-slate-700 grayscale hover:grayscale-0 transition-all cursor-default">KopiKenang</span>
                </div>
                @endfor
            </div>
        </div>
    </div>
</section>

{{-- ══════════════ FEATURES ══════════════ --}}
<section id="fitur" class="py-28 bg-white relative overflow-hidden">
    <div class="orb w-[450px] h-[450px] bg-green-100/70 -top-20 -right-20"></div>
    <div class="orb w-[300px] h-[300px] bg-emerald-100/60 bottom-0 left-0"></div>

    <div class="container mx-auto px-6 relative z-10">
        <div class="text-center max-w-2xl mx-auto mb-16" data-aos="fade-up">
            <span class="inline-block px-4 py-1.5 rounded-full bg-green-100 text-green-800 text-xs font-bold uppercase tracking-widest mb-4">Fitur Unggulan</span>
            <h2 class="text-4xl md:text-5xl font-outfit font-black text-slate-900 leading-tight mb-4">Semua yang Anda butuhkan, <span style="background:linear-gradient(135deg,#15803d,#22c55e);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">satu platform.</span></h2>
            <p class="text-slate-500 text-lg leading-relaxed">Tinggalkan cara manual. Kelola bisnis Anda lebih efisien dengan fitur-fitur canggih yang mudah dipahami.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-5xl mx-auto">
            @foreach([
                ['icon'=>'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z','color'=>'green','title'=>'Point of Sale Cepat','desc'=>'Antarmuka kasir yang super intuitif. Catat pesanan, proses pembayaran, dan cetak struk hanya dalam hitungan detik.','delay'=>100],
                ['icon'=>'M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z','color'=>'emerald','title'=>'Laporan Analitik','desc'=>'Dashboard laporan penjualan real-time. Grafik harian, produk terlaris, dan tren omzet selalu dalam genggaman Anda.','delay'=>150],
                ['icon'=>'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z','color'=>'lime','title'=>'Multi Kasir & Role','desc'=>'Tambahkan kasir tanpa batas. Atur hak akses setiap karyawan dengan mudah tanpa khawatir data bocor.','delay'=>200],
                ['icon'=>'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4','color'=>'teal','title'=>'Manajemen Stok','desc'=>'Pantau inventaris secara real-time. Notifikasi otomatis saat stok hampir habis agar tidak ada penjualan yang terlewat.','delay'=>250],
                ['icon'=>'M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z','color'=>'green','title'=>'Akses dari Mana Saja','desc'=>'Platform berbasis cloud yang bisa diakses dari HP, tablet, atau laptop — kapanpun dan dimanapun Anda butuhkan.','delay'=>300],
                ['icon'=>'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z','color'=>'emerald','title'=>'Laporan Harian via WhatsApp','desc'=>'Kirim laporan penjualan harian secara otomatis ke WhatsApp Anda setiap akhir hari — tanpa perlu buka aplikasi.','delay'=>350],
            ] as $feat)
            @php
                $colors = [
                    'green'  => ['bg'=>'bg-green-50',  'icon_bg'=>'bg-green-100',  'icon_text'=>'text-green-700', 'border'=>'border-green-100'],
                    'emerald'=> ['bg'=>'bg-emerald-50','icon_bg'=>'bg-emerald-100','icon_text'=>'text-emerald-700','border'=>'border-emerald-100'],
                    'lime'   => ['bg'=>'bg-lime-50',   'icon_bg'=>'bg-lime-100',   'icon_text'=>'text-lime-700',  'border'=>'border-lime-100'],
                    'teal'   => ['bg'=>'bg-teal-50',   'icon_bg'=>'bg-teal-100',   'icon_text'=>'text-teal-700',  'border'=>'border-teal-100'],
                ];
                $c = $colors[$feat['color']];
                $aosTypes = ['fade-right', 'fade-up', 'fade-left', 'fade-right', 'fade-up', 'fade-left'];
            @endphp
            <div class="feature-card tilt-element bg-white rounded-3xl p-7 border border-slate-100 shadow-sm hover:border-green-200" data-aos="{{ $aosTypes[$loop->index] }}" data-aos-delay="{{ $feat['delay'] }}">
                <div class="w-14 h-14 rounded-2xl {{ $c['icon_bg'] }} flex items-center justify-center {{ $c['icon_text'] }} mb-5">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $feat['icon'] }}"/></svg>
                </div>
                <h3 class="text-lg font-outfit font-bold text-slate-900 mb-2">{{ $feat['title'] }}</h3>
                <p class="text-slate-500 text-sm leading-relaxed">{{ $feat['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ══════════════ HOW IT WORKS ══════════════ --}}
<section id="cara-kerja" class="py-28 bg-gradient-to-br from-green-50 via-white to-emerald-50/80 relative overflow-hidden">
    <div class="orb w-[400px] h-[400px] bg-green-200/40 top-0 left-1/2 -translate-x-1/2"></div>

    <div class="container mx-auto px-6 relative z-10">
        <div class="text-center max-w-xl mx-auto mb-20" data-aos="fade-up">
            <span class="inline-block px-4 py-1.5 rounded-full bg-green-100 text-green-800 text-xs font-bold uppercase tracking-widest mb-4">Cara Kerja</span>
            <h2 class="text-4xl md:text-5xl font-outfit font-black text-slate-900 leading-tight">Mulai berjualan dalam <span style="background:linear-gradient(135deg,#15803d,#22c55e);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">3 langkah.</span></h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-4xl mx-auto">
            @foreach([
                ['num'=>'1','title'=>'Daftar Akun','desc'=>'Registrasi dalam 2 menit. Verifikasi email, isi data toko, dan akun Anda langsung aktif.','icon'=>'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z','delay'=>100],
                ['num'=>'2','title'=>'Setup Toko & Produk','desc'=>'Tambahkan informasi toko, buat akun kasir, dan daftarkan produk Anda (bisa impor dari Excel).','icon'=>'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z','delay'=>300],
                ['num'=>'3','title'=>'Mulai Transaksi','desc'=>'Kasir langsung siap menerima pesanan. Laporan omzet otomatis terkompilasi setiap harinya.','icon'=>'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z','delay'=>500],
            ] as $step)
            <div class="relative text-center group" data-aos="flip-left" data-aos-delay="{{ $step['delay'] }}">
                <div class="w-20 h-20 mx-auto rounded-full bg-white shadow-md border border-green-100 flex items-center justify-center mb-6 group-hover:shadow-lg group-hover:border-green-300 transition-all duration-300 relative z-10">
                    <span class="text-2xl font-outfit font-black" style="background:linear-gradient(135deg,#15803d,#22c55e);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">{{ $step['num'] }}</span>
                </div>
                <div class="bg-white rounded-3xl p-7 border border-slate-100 shadow-sm group-hover:shadow-md group-hover:border-green-100 transition-all duration-300">
                    <div class="w-12 h-12 rounded-2xl bg-green-50 flex items-center justify-center text-green-700 mx-auto mb-4">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $step['icon'] }}"/></svg>
                    </div>
                    <h3 class="text-lg font-outfit font-bold text-slate-900 mb-2">{{ $step['title'] }}</h3>
                    <p class="text-slate-500 text-sm leading-relaxed">{{ $step['desc'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ══════════════ TESTIMONIALS ══════════════ --}}
<section id="testimoni" class="py-28 bg-white relative overflow-hidden">
    <div class="orb w-[400px] h-[400px] bg-green-100/60 -bottom-20 -right-20"></div>

    <div class="container mx-auto px-6 relative z-10">
        <div class="text-center max-w-xl mx-auto mb-16" data-aos="fade-up">
            <span class="inline-block px-4 py-1.5 rounded-full bg-green-100 text-green-800 text-xs font-bold uppercase tracking-widest mb-4">Testimoni</span>
            <h2 class="text-4xl md:text-5xl font-outfit font-black text-slate-900 leading-tight">Kata mereka yang telah <span style="background:linear-gradient(135deg,#15803d,#22c55e);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">merasakan.</span></h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-5xl mx-auto">
            @foreach([
                ['name'=>'Budi Santoso','role'=>'Owner Warung Makan Pak Budi, Malang','quote'=>'Semenjak pakai SiKaMo, saya bisa pantau penjualan langsung dari rumah. Tidak perlu lagi nunggu laporan mingguan dari kasir, semua real-time!','avatar'=>'👨‍🍳','delay'=>100,'featured'=>false, 'aos'=>'fade-right'],
                ['name'=>'Siti Rahayu','role'=>'Owner Butik Fashion Cantik, Surabaya','quote'=>'Fitur multi kasirnya sangat membantu banget! Saya punya 3 kasir dan semuanya terpantau dari satu dashboard. Laporan hariannya akurat.','avatar'=>'👩‍💼','delay'=>300,'featured'=>true, 'aos'=>'zoom-in'],
                ['name'=>'Ahmad Rifai','role'=>'Manager Toko Elektronik Maju, Jakarta','quote'=>'Laporan analitiknya mudah dipahami bahkan oleh karyawan baru. Sangat membantu saat rapat bulanan untuk evaluasi performa toko.','avatar'=>'👨‍💻','delay'=>500,'featured'=>false, 'aos'=>'fade-left'],
            ] as $t)
            <div class="testi-card tilt-element rounded-3xl p-7 border {{ $t['featured'] ? 'bg-gradient-to-br from-green-600 to-emerald-700 border-green-700 text-white shadow-xl shadow-green-900/20 transform scale-105 z-10' : 'bg-white border-slate-100 shadow-sm' }}" data-aos="{{ $t['aos'] }}" data-aos-delay="{{ $t['delay'] }}">
                <div class="flex gap-1 mb-5">
                    @for($i=0;$i<5;$i++)
                    <svg class="w-4 h-4 {{ $t['featured'] ? 'text-yellow-300' : 'text-amber-400' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    @endfor
                </div>
                <p class="{{ $t['featured'] ? 'text-green-50' : 'text-slate-600' }} text-sm leading-relaxed mb-6">
                    "{{ $t['quote'] }}"
                </p>
                <div class="flex items-center gap-3 pt-5 border-t {{ $t['featured'] ? 'border-green-500/40' : 'border-slate-100' }}">
                    <div class="w-11 h-11 rounded-full {{ $t['featured'] ? 'bg-white/20' : 'bg-green-50' }} flex items-center justify-center text-xl">{{ $t['avatar'] }}</div>
                    <div>
                        <p class="{{ $t['featured'] ? 'text-white' : 'text-slate-900' }} font-bold text-sm">{{ $t['name'] }}</p>
                        <p class="{{ $t['featured'] ? 'text-green-200' : 'text-slate-400' }} text-xs">{{ $t['role'] }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ══════════════ FAQ ══════════════ --}}
<section id="faq" class="py-28 bg-slate-50/80 relative">
    <div class="container mx-auto px-6 max-w-3xl">
        <div class="text-center mb-16" data-aos="fade-up">
            <span class="inline-block px-4 py-1.5 rounded-full bg-green-100 text-green-800 text-xs font-bold uppercase tracking-widest mb-4">FAQ</span>
            <h2 class="text-4xl md:text-5xl font-outfit font-black text-slate-900 leading-tight">Pertanyaan yang <span style="background:linear-gradient(135deg,#15803d,#22c55e);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">sering ditanyakan.</span></h2>
        </div>

        <div class="space-y-3" id="faq-list">
            @foreach([
                ['q'=>'Apakah SiKaMo bisa digunakan secara offline?','a'=>'Bisa untuk kebutuhan kasir inti. Setelah login dan data toko tersimpan di perangkat, kasir dapat menjual produk, memakai data produk/kategori/member terakhir, mencetak struk, dan menyimpan transaksi pending. Saat internet kembali, transaksi disinkronkan ke server.'],
                ['q'=>'Apakah ada batasan jumlah kasir yang bisa ditambahkan?','a'=>'Tidak ada batasan sama sekali! Anda dapat menambahkan akun kasir sebanyak kebutuhan bisnis Anda, semuanya terhubung ke satu dashboard merchant utama.'],
                ['q'=>'Berapa biaya berlangganan SiKaMo?','a'=>'SiKaMo menawarkan free trial dengan fitur lengkap. Setelah masa trial, tersedia paket berlangganan mulai dari Rp 99.000/bulan. Tidak perlu kartu kredit untuk memulai.'],
                ['q'=>'Apakah data transaksi saya aman?','a'=>'Sangat aman. Semua data dienkripsi dengan standar SSL/TLS, disimpan di server tersertifikasi, dan di-backup otomatis setiap hari untuk keamanan data Anda.'],
                ['q'=>'Bisakah SiKaMo diintegrasikan dengan printer struk?','a'=>'Ya! SiKaMo mendukung printer thermal yang kompatibel melalui koneksi Bluetooth maupun jaringan lokal, siap langsung digunakan tanpa konfigurasi rumit.'],
            ] as $idx => $item)
            <div class="faq-item bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm hover:shadow-md transition-shadow" data-aos="fade-left" data-aos-delay="{{ $idx * 150 }}">
                <button onclick="toggleFaq(this)" class="w-full text-left px-6 py-5 flex justify-between items-start gap-4 hover:bg-green-50/50 transition-colors duration-200">
                    <span class="font-outfit font-bold text-slate-900 text-[15px] leading-snug">{{ $item['q'] }}</span>
                    <svg class="faq-arrow w-5 h-5 text-slate-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div class="faq-body hidden px-6 pb-5 text-slate-500 text-sm leading-relaxed border-t border-slate-100 pt-4">
                    {{ $item['a'] }}
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ══════════════ CTA ══════════════ --}}
<section class="py-24 relative overflow-hidden bg-gradient-to-br from-green-600 via-green-700 to-emerald-800">
    <div class="orb w-[500px] h-[500px] bg-white/10 -top-20 -right-20"></div>
    <div class="orb w-[350px] h-[350px] bg-lime-400/20 bottom-0 left-0"></div>

    {{-- Pattern dots --}}
    <div class="absolute inset-0 opacity-10" style="background-image:radial-gradient(#fff 1px,transparent 1px);background-size:24px 24px;"></div>

    <div class="container mx-auto px-6 relative z-10">
        <div class="max-w-3xl mx-auto text-center" data-aos="zoom-out-up" data-aos-duration="1000">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/15 border border-white/25 text-white text-sm font-semibold mb-8 backdrop-blur-sm shadow-[0_0_20px_rgba(255,255,255,0.1)]">
                <svg class="w-4 h-4 text-yellow-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Daftar gratis, aktif dalam 2 menit
            </div>

            <h2 class="text-4xl md:text-5xl font-outfit font-black text-white leading-tight mb-6">
                Siap bawa bisnis Anda ke level berikutnya?
            </h2>
            <p class="text-green-100 text-xl mb-12 max-w-xl mx-auto leading-relaxed">
                Bergabunglah dengan 2.500+ pelaku UMKM yang sudah merasakan kemudahan SiKaMo.
            </p>

            @auth
                @php $dashboardUrl = auth()->user()->role === 'admin' ? '/admin' : '/merchant'; @endphp
                <a href="{{ $dashboardUrl }}" id="cta-btn" class="magnetic-btn inline-flex items-center gap-3 px-10 py-5 rounded-2xl bg-white text-green-800 font-black text-xl shadow-[0_10px_40px_rgba(0,0,0,0.2)] hover:shadow-[0_20px_50px_rgba(0,0,0,0.3)] transition-all duration-300 z-10 relative cursor-pointer">
                    Buka Dashboard Saya
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </a>
            @else
                <a href="/login" id="cta-btn" class="magnetic-btn inline-flex items-center gap-3 px-10 py-5 rounded-2xl bg-white text-green-800 font-black text-xl shadow-[0_10px_40px_rgba(0,0,0,0.2)] hover:shadow-[0_20px_50px_rgba(0,0,0,0.3)] transition-all duration-300 z-10 relative cursor-pointer">
                    Mulai Gratis Sekarang
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </a>
                <p class="text-green-200/70 text-sm mt-5">Tidak perlu kartu kredit · Gratis untuk 1 kasir selamanya</p>
            @endauth
        </div>
    </div>
</section>

{{-- ══════════════ FOOTER ══════════════ --}}
<footer class="bg-slate-900 text-white py-16">
    <div class="container mx-auto px-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
            <div class="md:col-span-2">
                <div class="flex items-center gap-2.5 mb-4">
                    <img src="{{ asset('assets/logo.svg') }}" alt="SiKaMo" class="h-8 w-auto">
                    <span class="font-outfit font-black text-xl text-white">SiKaMo<span class="text-green-400">.</span></span>
                </div>
                <p class="text-slate-400 text-sm leading-relaxed max-w-xs">
                    Platform kasir digital modern untuk UMKM Indonesia. Mudah, cepat, dan terpercaya.
                </p>
            </div>
            <div>
                <h4 class="text-white font-semibold text-sm mb-5">Produk</h4>
                <ul class="space-y-3">
                    <li><a href="#fitur" class="text-slate-400 hover:text-green-400 text-sm transition-colors">Fitur</a></li>
                    <li><a href="#cara-kerja" class="text-slate-400 hover:text-green-400 text-sm transition-colors">Cara Kerja</a></li>
                    <li><a href="#testimoni" class="text-slate-400 hover:text-green-400 text-sm transition-colors">Testimoni</a></li>
                    <li><a href="#faq" class="text-slate-400 hover:text-green-400 text-sm transition-colors">FAQ</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-semibold text-sm mb-5">Akun</h4>
                <ul class="space-y-3">
                    <li><a href="/login" class="text-slate-400 hover:text-green-400 text-sm transition-colors">Masuk</a></li>
                    <li><a href="/login" class="text-slate-400 hover:text-green-400 text-sm transition-colors">Daftar</a></li>
                </ul>
            </div>
        </div>
        <div class="border-t border-white/10 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
            <p class="text-slate-500 text-sm">© <?php echo date('Y') ?> SiKaMo. All rights reserved.</p>
            <div class="flex gap-3">
                <a href="#" class="w-9 h-9 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center text-slate-400 hover:text-white hover:bg-white/10 transition-all">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84"/></svg>
                </a>
                <a href="#" class="w-9 h-9 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center text-slate-400 hover:text-white hover:bg-white/10 transition-all">
                    <svg fill="currentColor" viewBox="0 0 24 24" class="w-4 h-4"><path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd"/></svg>
                </a>
            </div>
        </div>
    </div>
</footer>

<!-- Scripts for Animations & Interactivity -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@srexi/purecounterjs/dist/purecounter_vanilla.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/vanilla-tilt/1.8.1/vanilla-tilt.min.js"></script>
<script src="https://unpkg.com/typed.js@2.1.0/dist/typed.umd.js"></script>
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

<script>
// ── Animations Initialization ───────────────────────────────
AOS.init({ 
    duration: 1000, 
    once: true, 
    offset: 100,
    easing: 'ease-out-back' 
});

new PureCounter();

VanillaTilt.init(document.querySelectorAll(".tilt-element"), {
    max: 8,
    speed: 400,
    glare: true,
    "max-glare": 0.15,
});

if(document.getElementById('typed-text')) {
    new Typed('#typed-text', {
        strings: ['yang Cerdas', 'yang Modern', 'yang Cepat', 'yang Praktis'],
        typeSpeed: 60,
        backSpeed: 40,
        backDelay: 2000,
        loop: true,
        showCursor: true,
        cursorChar: '|'
    });
}

// ── Interactivity Enhancements ────────────────────────────

// 1. Scroll Progress Bar
window.addEventListener('scroll', () => {
    const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
    const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
    const scrolled = (winScroll / height) * 100;
    const progress = document.getElementById('scroll-progress');
    if(progress) progress.style.width = scrolled + "%";
});

// 2. Magnetic Buttons
document.querySelectorAll('.magnetic-btn').forEach(btn => {
    btn.addEventListener('mousemove', (e) => {
        const rect = btn.getBoundingClientRect();
        const x = e.clientX - rect.left - rect.width / 2;
        const y = e.clientY - rect.top - rect.height / 2;
        btn.style.transform = `translate(${x * 0.25}px, ${y * 0.25}px) scale(1.02)`;
    });
    btn.addEventListener('mouseleave', () => {
        btn.style.transform = 'translate(0px, 0px) scale(1)';
    });
});

// 3. Live Mockup Chart Animation
setInterval(() => {
    document.querySelectorAll('.mockup-bar').forEach(bar => {
        const randomHeight = Math.floor(Math.random() * 65) + 25; // 25% to 90%
        bar.style.height = `${randomHeight}%`;
    });
}, 2000);

// 4. Confetti on CTA Hover
const ctaBtn = document.getElementById('cta-btn');
if(ctaBtn) {
    let confettiFired = false;
    ctaBtn.addEventListener('mouseenter', () => {
        if(!confettiFired) {
            confetti({
                particleCount: 80,
                spread: 70,
                origin: { y: 0.8 },
                colors: ['#22c55e', '#10b981', '#bef264', '#ffffff'],
                disableForReducedMotion: true
            });
            confettiFired = true;
            setTimeout(() => confettiFired = false, 3000); // Prevent spamming
        }
    });
}

// ── Navbar scroll ─────────────────────────────────────────
const navbar = document.getElementById('navbar');
window.addEventListener('scroll', () => {
    navbar.classList.toggle('scrolled', window.scrollY > 40);
});

// ── Mobile menu ───────────────────────────────────────────
document.getElementById('mobile-toggle').addEventListener('click', () => {
    document.getElementById('mobile-menu').classList.toggle('open');
});
document.querySelectorAll('.mobile-link').forEach(l =>
    l.addEventListener('click', () => document.getElementById('mobile-menu').classList.remove('open'))
);

// ── FAQ accordion ─────────────────────────────────────────
window.toggleFaq = function(btn) {
    const body  = btn.nextElementSibling;
    const arrow = btn.querySelector('.faq-arrow');
    const open  = !body.classList.contains('hidden');

    document.querySelectorAll('.faq-body').forEach(b => b.classList.add('hidden'));
    document.querySelectorAll('.faq-arrow').forEach(a => a.classList.remove('open'));

    if (!open) {
        body.classList.remove('hidden');
        arrow.classList.add('open');
    }
};

// ── Smooth active nav highlight ───────────────────────────
const sections = document.querySelectorAll('section[id]');
const navLinks  = document.querySelectorAll('nav a[href^="#"]');
window.addEventListener('scroll', () => {
    let current = '';
    sections.forEach(s => {
        if (window.scrollY >= s.offsetTop - 120) current = s.id;
    });
    navLinks.forEach(link => {
        link.classList.toggle('text-green-700', link.getAttribute('href') === '#' + current);
        link.classList.toggle('text-slate-500', link.getAttribute('href') !== '#' + current);
    });
}, { passive: true });

// ── PWA Setup ─────────────────────────────────────────────
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').then(reg => {
            console.log('ServiceWorker registered:', reg.scope);
        }).catch(err => {
            console.log('ServiceWorker registration failed:', err);
        });
    });
}

let deferredPrompt;
const installBtnAuth = document.getElementById('install-pwa-btn');
const installBtnGuest = document.getElementById('install-pwa-btn-guest');

window.addEventListener('beforeinstallprompt', (e) => {
    // Prevent Chrome 67 and earlier from automatically showing the prompt
    e.preventDefault();
    // Stash the event so it can be triggered later.
    deferredPrompt = e;
    // Update UI to notify the user they can add to home screen
    if (installBtnAuth) installBtnAuth.classList.remove('hidden');
    if (installBtnGuest) installBtnGuest.classList.remove('hidden');
});

const installApp = async () => {
    if (deferredPrompt) {
        deferredPrompt.prompt();
        const { outcome } = await deferredPrompt.userChoice;
        if (outcome === 'accepted') {
            console.log('User accepted the A2HS prompt');
        } else {
            console.log('User dismissed the A2HS prompt');
        }
        deferredPrompt = null;
        if (installBtnAuth) installBtnAuth.classList.add('hidden');
        if (installBtnGuest) installBtnGuest.classList.add('hidden');
    }
};

if (installBtnAuth) installBtnAuth.addEventListener('click', installApp);
if (installBtnGuest) installBtnGuest.addEventListener('click', installApp);

window.addEventListener('appinstalled', (evt) => {
    console.log('A2HS installed');
    if (installBtnAuth) installBtnAuth.classList.add('hidden');
    if (installBtnGuest) installBtnGuest.classList.add('hidden');
});
</script>

</body>
</html>
