<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Persona' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <!-- Font Awesome 6.4 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-p1CmS2Gq1YwG2s9nQ/7v6e0Q8nYfJb+9C8l3b3kSxQf9uFJQk2xw1jQY7xwIYd3s6p8g7HnKqv3HhH6DkLrYw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        :root{
            --bg-start:#0b1220; --bg-end:#0d2136;
            --primary:#38bdf8; /* sky blue */
            --primary-2:#22d3ee; /* teal-ish */
            --accent:#f59e0b; /* amber */
            --gold:#fbbf24;
            --surface: rgba(255,255,255,0.06);
            --glass: rgba(255,255,255,0.08);
            --text:#e5e7eb; --muted:#9ca3af;
            --success:#10b981; --danger:#ef4444; --warning:#f59e0b;
            --shadow: 0 10px 30px rgba(0,0,0,0.35);
            --radius: 14px;
            --grad-primary: linear-gradient(135deg, var(--primary), var(--primary-2));
            --grad-accent: linear-gradient(135deg, var(--accent), var(--gold));
        }
        *{box-sizing:border-box}
        html,body{height:100%}
        body{
            margin:0; font-family: Segoe UI, Roboto, Helvetica, Arial, sans-serif;
            color:var(--text);
            background: radial-gradient(1200px 800px at 20% -10%, #0f1b2d 0%, transparent 60%),
                        linear-gradient(180deg, var(--bg-start), var(--bg-end));
            overflow-x:hidden;
        }
        /* Animated clouds */
        .clouds{
            position:fixed; inset:0; z-index:-1; overflow:hidden; pointer-events:none;
        }
        .clouds::before, .clouds::after{
            content:""; position:absolute; width:1600px; height:1600px; border-radius:50%;
            background: radial-gradient(circle at 30% 30%, rgba(56,189,248,0.12), transparent 60%),
                        radial-gradient(circle at 70% 70%, rgba(34,211,238,0.12), transparent 60%);
            filter: blur(80px);
            animation: float 26s ease-in-out infinite;
        }
        .clouds::after{ left:-20%; top:30%; animation-duration: 34s; }
        @keyframes float{ 0%,100%{ transform: translateY(0) } 50%{ transform: translateY(-30px) } }

        /* Navbar */
      .nav{ position:fixed; top:0; left:0; right:0; z-index:40; backdrop-filter: blur(10px);
          background: rgba(10,17,30,0.85);
              border-bottom:1px solid rgba(255,255,255,0.06);
        }
        .nav-inner{ max-width:1200px; margin:0 auto; padding:14px 20px; display:flex; align-items:center; justify-content:space-between; }
        .brand{ display:flex; align-items:center; gap:10px; font-weight:700; letter-spacing:.3px; }
        .brand i{ color: var(--primary); text-shadow:0 0 12px rgba(56,189,248,.6); }
        .menu{ display:flex; gap:18px; align-items:center; }
        .menu a{ color:var(--muted); text-decoration:none; position:relative; padding:6px 8px; border-radius:10px; }
        .menu a:hover{ color:var(--text); }
        .menu a::after{ content:""; position:absolute; left:8px; right:8px; bottom:2px; height:2px; background:var(--grad-primary); transform:scaleX(0); transform-origin:left; transition:transform .3s; border-radius:2px; }
        .menu a:hover::after{ transform:scaleX(1) }
        .hamb{ display:none; font-size:20px; }
        .nav.scrolled{ box-shadow: var(--shadow); background: rgba(10,17,30,0.85); }

        /* Hero */
        .hero{ padding-top:110px; padding-bottom:30px; }
        .hero-inner{ max-width:1200px; margin:0 auto; padding:0 20px; display:grid; grid-template-columns: 1.3fr .7fr; gap:24px; align-items:stretch; }
        .card{ background: var(--glass); border:1px solid rgba(255,255,255,0.06); border-radius: var(--radius); box-shadow: var(--shadow); }
        .card.pad{ padding:22px; }
        .title{ font-size:28px; margin:0 0 10px; }
        .subtitle{ color:var(--muted); margin:0 0 18px; }
        .flight-form{ display:grid; grid-template-columns: repeat(4,1fr); gap:12px; }
        .input, .select{ background: rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.08); color:var(--text); padding:12px 12px; border-radius:12px; outline:none; }
        .btn{ display:inline-flex; gap:8px; align-items:center; justify-content:center; background:var(--grad-primary); color:#0a0f1a; font-weight:600; padding:12px 14px; border-radius:12px; border:none; cursor:pointer; position:relative; overflow:hidden; }
        .btn::before{ content:""; position:absolute; inset:0; background: linear-gradient(120deg, rgba(255,255,255,.35), transparent 40%); transform: translateX(-100%); transition: transform .6s; }
        .btn:hover::before{ transform: translateX(0) }
        .btn.secondary{ background: var(--glass); color:var(--text); border:1px solid rgba(255,255,255,0.12) }
        .badge{ padding:6px 10px; border-radius:999px; background: rgba(16,185,129,.12); color:#a7f3d0; font-weight:600; border:1px solid rgba(16,185,129,.25) }

    /* Actions styling */
        .actions{ max-width:1200px; margin:18px auto 0; padding:0 20px; display:grid; grid-template-columns: repeat(5,1fr); gap:14px; }
        .action{ text-decoration:none; color:var(--text); display:flex; align-items:center; gap:12px; padding:16px; border-radius:14px; background: var(--surface); border:1px solid rgba(255,255,255,0.06); box-shadow: var(--shadow); transition: transform .2s, box-shadow .2s; }
        .action:hover{ transform: translateY(-2px); box-shadow: 0 16px 40px rgba(0,0,0,.45); }
        .action i{ color:var(--primary) }

        /* Stats */
    .stats{ max-width:1200px; margin:20px auto; padding:0 20px; display:grid; grid-template-columns: repeat(3,1fr); gap:16px; }
    .stats.stats-4{ grid-template-columns: repeat(4, 1fr); }
        .stat{ padding:18px; border-radius:14px; background: var(--glass); border:1px solid rgba(255,255,255,.06); box-shadow: var(--shadow); min-height:110px; display:flex; flex-direction:column; justify-content:space-between; }
        .stat .label{ color:var(--muted); font-size:13px }
        .stat .value{ font-size:28px; font-weight:700; margin-top:6px; }
        .stat.income .value{ color:#86efac }
        .stat.expense .value{ color:#fca5a5 }
        .stat.balance .value{ color:#93c5fd }

    /* Utilities */
    .page-grid{ max-width:1200px; margin:0 auto; padding:0 20px; display:grid; grid-template-columns: 2fr 1fr; gap:16px; }
    .chart-legend{ display:grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap:10px; }

    /* Features */
        .features{ max-width:1200px; margin:14px auto; padding:0 20px; display:grid; grid-template-columns: repeat(3,1fr); gap:16px; }
        .feature{ padding:18px; border-radius:14px; background: var(--surface); border:1px solid rgba(255,255,255,.06); box-shadow: var(--shadow); position:relative; overflow:hidden; }
        .feature::before{ content:""; position:absolute; inset:0; background: radial-gradient(400px 200px at -10% -10%, rgba(56,189,248,.12), transparent 50%); pointer-events:none }
        .feature h3{ margin:0 0 6px }
        .feature p{ color:var(--muted); margin:0 }

        /* Newsletter */
        .newsletter{ max-width:1200px; margin:16px auto; padding:0 20px; }
        .newsletter .wrap{ display:flex; flex-wrap:wrap; gap:12px; align-items:center; justify-content:space-between; padding:18px; border-radius:16px; background: linear-gradient(135deg, rgba(56,189,248,.12), rgba(245,158,11,.10)); border:1px solid rgba(255,255,255,.08) }
        .newsletter .field{ flex:1; min-width:220px; }
        .newsletter input{ width:100%; }

        /* Footer */
        .footer{ max-width:1200px; margin:30px auto; padding:0 20px 40px; display:flex; flex-wrap:wrap; gap:18px; align-items:center; justify-content:space-between; color:var(--muted) }

        /* Toast */
        .toast{ position:fixed; right:20px; top:80px; padding:12px 14px; border-radius:12px; background: rgba(16,185,129,.18); color:#d1fae5; border:1px solid rgba(16,185,129,.35); box-shadow: var(--shadow); animation: slideInRight .4s ease, fadeOut .4s ease 4.2s forwards; z-index:50; }
        @keyframes slideInRight{ from{ transform:translateX(100%); opacity:.4 } to{ transform:translateX(0); opacity:1 } }
        @keyframes fadeOut{ to{ opacity:0; transform: translateX(8px) } }

        /* Scroll to top */
        .to-top{ position:fixed; right:18px; bottom:18px; width:44px; height:44px; display:grid; place-items:center; border-radius:999px; background: var(--grad-primary); color:#0a0f1a; border:none; box-shadow: var(--shadow); cursor:pointer; opacity:0; pointer-events:none; transition: opacity .2s, transform .2s; z-index:40; }
        .to-top.show{ opacity:1; pointer-events:auto; transform: translateY(-4px) }

    /* Responsive */
    @media (max-width: 1200px){ .hero-inner{ grid-template-columns:1fr } .actions{ grid-template-columns: repeat(3,1fr) } .stats{ grid-template-columns: repeat(2,1fr); } .page-grid{ grid-template-columns: 1fr; } }
        @media (max-width: 768px){ .menu{ display:none } .hamb{ display:block } .menu.active{ display:flex; position:absolute; top:60px; right:18px; flex-direction:column; background:var(--glass); border:1px solid rgba(255,255,255,.08); border-radius:12px; padding:8px; gap:6px }
            .flight-form{ grid-template-columns: 1fr 1fr; }
            .features{ grid-template-columns: 1fr; }
            .stats{ grid-template-columns: 1fr; }
            .actions{ grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 480px){ .actions{ grid-template-columns: 1fr } .flight-form{ grid-template-columns: 1fr } }
    </style>
</head>
<body id="top">
    <div class="clouds"></div>

    <nav class="nav" id="nav">
        <div class="nav-inner">
            <a class="brand" href="{{ url('/') }}" aria-label="Home">
                <i class="fa-solid fa-jet-fighter-up"></i>
                <span>Persona</span>
            </a>
            <div class="menu" id="menu">
                <a href="{{ route('dashboard') }}">Dashboard</a>
                <a href="{{ route('finance.dashboard') }}">Finance</a>
                <a href="{{ route('tasks.index') }}">Tasks</a>
                <a href="{{ route('reports.index') }}">Reports</a>
                <a href="{{ route('settings.index') }}">Settings</a>
                @auth
                    <a href="{{ route('profile.edit') }}"><i class="fa-regular fa-user"></i> Profile</a>
                @else
                    <a href="{{ route('login') }}"><i class="fa-solid fa-right-to-bracket"></i> Login</a>
                @endauth
            </div>
            <button class="hamb" id="hamb" aria-label="Menu"><i class="fa-solid fa-bars"></i></button>
        </div>
    </nav>

    @if(session('success'))
        <div class="toast" role="status" aria-live="polite">
            <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
        </div>
    @endif

    <!-- Hero with search form (hidden for content-only pages) -->
    @if(!View::hasSection('content_only'))
    <header class="hero">
        <div class="hero-inner">
            <section class="card pad">
                <div class="badge"><i class="fa-solid fa-wave-square"></i> Welcome back</div>
                <h1 class="title">Plan, track, and analyze effortlessly</h1>
                <p class="subtitle">Quickly search transactions or jump into tasks. The new UI is fast, focused, and beautiful.</p>
                <form class="flight-form" action="{{ route('finance.transactions.index') }}" method="get" id="searchForm">
                    <input class="input" type="text" name="search" placeholder="Search transactions (e.g. coffee, 25, Walmart)"/>
                    <select class="select" name="type">
                        <option value="">All types</option>
                        <option value="income">Income</option>
                        <option value="expense">Expense</option>
                    </select>
                    <input class="input" type="date" name="start_date"/>
                    <input class="input" type="date" name="end_date"/>
                    <button class="btn" type="submit" id="searchBtn"><i class="fa-solid fa-magnifying-glass"></i> <span class="btn-text">Search</span></button>
                </form>
            </section>
            <aside class="card pad">
                <h2 class="title" style="font-size:20px">Quick links</h2>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px">
                    <a class="action" href="{{ route('finance.transactions.create') }}"><i class="fa-solid fa-plus"></i> Add Transaction</a>
                    <a class="action" href="{{ route('finance.transactions.index') }}"><i class="fa-regular fa-file-lines"></i> All Transactions</a>
                    <a class="action" href="{{ route('tasks.create') }}"><i class="fa-solid fa-calendar-plus"></i> Add Task</a>
                    <a class="action" href="{{ route('tasks.index') }}"><i class="fa-regular fa-square-check"></i> My Tasks</a>
                    <a class="action" href="{{ route('reports.index') }}"><i class="fa-solid fa-chart-line"></i> Reports</a>
                    <a class="action" href="{{ route('settings.index') }}"><i class="fa-solid fa-gear"></i> Settings</a>
                </div>
            </aside>
        </div>
    </header>
    @endif

    <!-- Page content -->
    <main style="{{ View::hasSection('content_only') ? 'padding-top:90px' : '' }}">
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    <!-- Features (hidden for content-only pages) -->
    @if(!View::hasSection('content_only'))
    <section class="features">
        <div class="feature">
            <h3><i class="fa-regular fa-gem"></i> Beautiful UI</h3>
            <p>Dark, glassy, and responsive. Built for clarity and focus.</p>
        </div>
        <div class="feature">
            <h3><i class="fa-solid fa-bolt"></i> Fast Interactions</h3>
            <p>Micro-animations and subtle feedback without slowing you down.</p>
        </div>
        <div class="feature">
            <h3><i class="fa-solid fa-shield-halved"></i> Secure by default</h3>
            <p>Auth-gated routes and best practice patterns remain intact.</p>
        </div>
    </section>
    @endif

    <footer class="footer">
        <div>© <span id="year"></span> Persona</div>
        <div style="display:flex; gap:12px">
            <a class="menu" href="#top">Back to top</a>
        </div>
    </footer>

    <button class="to-top" id="toTop" aria-label="Scroll to top"><i class="fa-solid fa-arrow-up"></i></button>

    <script>
        // Mobile menu toggle
        const hamb = document.getElementById('hamb');
        const menu = document.getElementById('menu');
        hamb && hamb.addEventListener('click', ()=> menu.classList.toggle('active'));

        // Navbar scroll effect
        const nav = document.getElementById('nav');
        const onScroll = ()=>{ if(window.scrollY>100){ nav.classList.add('scrolled'); } else { nav.classList.remove('scrolled'); } };
        document.addEventListener('scroll', onScroll); onScroll();

        // Smooth in-page scrolling
        document.querySelectorAll('a[href^="#"]').forEach(a=>{
            a.addEventListener('click', e=>{ const id=a.getAttribute('href').slice(1); if(!id) return; const el=document.getElementById(id); if(el){ e.preventDefault(); el.scrollIntoView({behavior:'smooth'}); } });
        });

        // Action micro-interaction
        document.querySelectorAll('.action').forEach(el=>{
            el.addEventListener('mousedown', ()=>{ el.style.transform='scale(.98)'; });
            el.addEventListener('mouseup', ()=>{ el.style.transform=''; });
            el.addEventListener('mouseleave', ()=>{ el.style.transform=''; });
        });

        // Stats counter (IntersectionObserver)
        const withSuffix=(n)=>n.toLocaleString();
        const runCounter=(el)=>{
            const target= Number(el.dataset.target||0);
            const duration=1000; const start=0; const step=16; let cur= start; const inc= Math.max(1, Math.round(target/(duration/step)));
            const timer= setInterval(()=>{ cur+= inc; if(cur>=target){cur=target; clearInterval(timer);} el.textContent= withSuffix(cur); }, step);
        };
        const io= new IntersectionObserver(entries=>{ entries.forEach(e=>{ if(e.isIntersecting){ runCounter(e.target); io.unobserve(e.target); } }); });
        document.querySelectorAll('[data-counter]').forEach(el=> io.observe(el));

        // Scroll to top
        const toTop=document.getElementById('toTop');
        const showTop=()=>{ if(window.scrollY>300){ toTop.classList.add('show'); } else { toTop.classList.remove('show'); } };
        document.addEventListener('scroll', showTop); showTop();
        toTop && toTop.addEventListener('click', ()=> window.scrollTo({top:0, behavior:'smooth'}));

        // Search form UX spinner
        const searchForm=document.getElementById('searchForm');
        const searchBtn=document.getElementById('searchBtn');
        if(searchForm && searchBtn){ searchForm.addEventListener('submit', ()=>{ const txt=searchBtn.querySelector('.btn-text'); if(txt){ txt.textContent='Searching…'; } }); }

        // Newsletter demo
        const newsletterBtn=document.getElementById('newsletterBtn');
        newsletterBtn && newsletterBtn.addEventListener('click', ()=>{
            const txt=newsletterBtn.querySelector('.btn-text'); if(txt){ txt.textContent='Subscribing…'; }
            setTimeout(()=>{ if(txt){ txt.textContent='Subscribed!'; } setTimeout(()=>{ if(txt){ txt.textContent='Subscribe'; } }, 1800); }, 1200);
        });

        // Year
        document.getElementById('year').textContent=new Date().getFullYear();
    </script>
</body>
</html>
