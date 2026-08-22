@extends('layouts.app')

@section('content')
<style>
    /* ── Hero Section ─────────────────────────────────── */
    .hero {
        text-align: center;
        max-width: 800px;
        margin: 0 auto 4rem;
    }

    .hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 5px 14px;
        border-radius: 999px;
        background: rgba(139, 92, 246, 0.08);
        border: 1px solid rgba(139, 92, 246, 0.25);
        color: #c4b5fd;
        font-size: 0.8rem;
        font-weight: 600;
        margin-bottom: 1.5rem;
    }

    .hero-badge span {
        color: #a78bfa;
    }

    .hero-title {
        font-family: 'Outfit', sans-serif;
        font-size: 3rem;
        line-height: 1.15;
        font-weight: 800;
        letter-spacing: -0.03em;
        margin-bottom: 1.25rem;
        background: linear-gradient(180deg, #ffffff 60%, #a1a1aa 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .hero-desc {
        font-size: 1.1rem;
        color: var(--text-muted);
        line-height: 1.6;
        margin-bottom: 2rem;
    }

    .hero-actions {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 14px;
        flex-wrap: wrap;
    }

    .btn-glow {
        box-shadow: 0 0 24px rgba(139, 92, 246, 0.35);
    }

    /* ── Quick Start Grid ─────────────────────────────── */
    .grid-section {
        margin-bottom: 4rem;
    }

    .section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.5rem;
        border-bottom: 1px solid var(--border-subtle);
        padding-bottom: 0.75rem;
    }

    .section-title {
        font-family: 'Outfit', sans-serif;
        font-size: 1.25rem;
        font-weight: 700;
        letter-spacing: -0.02em;
    }

    .cards-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 1.25rem;
    }

    .step-card {
        background: #121215;
        border: 1px solid var(--border-subtle);
        border-radius: 10px;
        padding: 1.5rem;
        transition: transform 0.2s ease, border-color 0.2s ease;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .step-card:hover {
        transform: translateY(-2px);
        border-color: #3f3f46;
    }

    .step-num {
        font-family: 'JetBrains Mono', monospace;
        font-size: 0.75rem;
        font-weight: 700;
        color: #a78bfa;
        margin-bottom: 0.75rem;
    }

    .step-title {
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: #fff;
    }

    .step-desc {
        font-size: 0.85rem;
        color: var(--text-muted);
        margin-bottom: 1.25rem;
        line-height: 1.5;
    }

    .code-pill {
        background: #09090b;
        border: 1px solid #27272a;
        padding: 8px 12px;
        border-radius: 6px;
        font-family: 'JetBrains Mono', monospace;
        font-size: 0.78rem;
        color: #38bdf8;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    /* ── Live UI Component Showcase ───────────────────── */
    .showcase-card {
        background: #121215;
        border: 1px solid var(--border-subtle);
        border-radius: 12px;
        padding: 2rem;
    }

    .showcase-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
    }

    @media (max-width: 768px) {
        .hero-title {
            font-size: 2.25rem;
        }
        .showcase-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-badge">
        <span>✦</span> The Modern PHP Framework You Actually Own
    </div>
    
    <h1 class="hero-title">
        A blazing-fast foundation for your next big idea.
    </h1>

    <p class="hero-desc">
        Zero-configuration setup with native Blade components, built-in ActiveRecord models, dynamic routing, and 21 monochrome UI components.
    </p>

    <div class="hero-actions">
        <a href="https://veldora.dev/docs" target="_blank" rel="noopener" class="vui-btn vui-btn-primary vui-btn-lg btn-glow">
            Explore Documentation →
        </a>
        <a href="https://veldora.dev/components" target="_blank" rel="noopener" class="vui-btn vui-btn-secondary vui-btn-lg">
            Browse 21 UI Components
        </a>
    </div>
</section>

<!-- Quick Start Grid -->
<section class="grid-section">
    <div class="section-header">
        <h2 class="section-title">Quick Start Guide</h2>
        <span style="font-size: 0.8rem; color: var(--text-muted); font-family: 'JetBrains Mono', monospace;">Ready in 5 seconds</span>
    </div>

    <div class="cards-grid">
        <!-- Card 1 -->
        <div class="step-card">
            <div>
                <div class="step-num">STEP 01</div>
                <h3 class="step-title">Development Server</h3>
                <p class="step-desc">Start the built-in HTTP dev server with hot templates and instant reload.</p>
            </div>
            <div class="code-pill">
                <span>php veldora serve</span>
            </div>
        </div>

        <!-- Card 2 -->
        <div class="step-card">
            <div>
                <div class="step-num">STEP 02</div>
                <h3 class="step-title">Generate Controllers</h3>
                <p class="step-desc">Scaffold clean HTTP controllers with action methods and request handling.</p>
            </div>
            <div class="code-pill">
                <span>php veldora make:controller PostController</span>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="step-card">
            <div>
                <div class="step-num">STEP 03</div>
                <h3 class="step-title">Add UI Components</h3>
                <p class="step-desc">Copy accessible, pre-styled components directly into your views directory.</p>
            </div>
            <div class="code-pill">
                <span>php veldora add button card modal</span>
            </div>
        </div>

        <!-- Card 4 -->
        <div class="step-card">
            <div>
                <div class="step-num">STEP 04</div>
                <h3 class="step-title">Database & Auth</h3>
                <p class="step-desc">Run migrations or scaffold a complete authentication system in one command.</p>
            </div>
            <div class="code-pill">
                <span>php veldora make:auth</span>
            </div>
        </div>
    </div>
</section>

<!-- Live UI Preview Section -->
<section class="grid-section">
    <div class="section-header">
        <h2 class="section-title">Veldora UI Components in Action</h2>
        <a href="https://veldora.dev/components" target="_blank" rel="noopener" style="font-size: 0.85rem; color: #a78bfa; text-decoration: none; font-weight: 500;">
            View all 21 components →
        </a>
    </div>

    <div class="showcase-card">
        <div class="showcase-grid">
            <!-- Demo 1 -->
            <div>
                <h4 style="font-size: 0.9rem; font-weight: 600; color: #fff; margin-bottom: 0.5rem;">Alert & Badge Components</h4>
                <p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 1rem;">Accessible notification banners and status badges.</p>

                <x-alert type="success" title="Application Ready">
                    Your Veldora application environment is properly configured.
                </x-alert>

                <div style="margin-top: 1rem; display: flex; gap: 8px; flex-wrap: wrap;">
                    <x-badge variant="primary">PHP 8.2+</x-badge>
                    <x-badge variant="secondary">Zero Config</x-badge>
                    <x-badge variant="outline">21 Components</x-badge>
                    <x-badge variant="success">ActiveRecord</x-badge>
                </div>
            </div>

            <!-- Demo 2 -->
            <div>
                <h4 style="font-size: 0.9rem; font-weight: 600; color: #fff; margin-bottom: 0.5rem;">Card & Action Buttons</h4>
                <p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 1rem;">Pre-styled dark-mode interactive cards.</p>

                <x-card :title="$postTitle" subtitle="Rendered dynamically from Controller">
                    <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.5; margin-bottom: 1rem;">
                        {{ $postBody }}
                    </p>
                    <div style="display: flex; gap: 10px;">
                        <x-button variant="primary" size="sm">Primary Action</x-button>
                        <x-button variant="secondary" size="sm">Secondary</x-button>
                    </div>
                </x-card>
            </div>
        </div>
    </div>
</section>

@endsection
