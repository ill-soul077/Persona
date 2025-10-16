<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="csrf-token" content="{{ csrf_token() }}">

	<title>{{ config('app.name', 'Persona') }} - Register</title>

	<link href="https://fonts.googleapis.com/css2?family=Segoe+UI:wght@300;400;500;600;700&display=swap" rel="stylesheet">
	@vite(['resources/css/app.css', 'resources/js/app.js'])

	<style>
		:root {
			--primary: #0ea5e9;
			--secondary: #06b6d4;
			--text: #f8fafc;
			--text-secondary: #cbd5e1;
			--bg-primary: #0f172a;
			--bg-glass: rgba(30, 41, 59, 0.7);
			--bg-glass-light: rgba(30, 41, 59, 0.5);
			--shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5);
			--shadow-glow: 0 0 20px rgba(14, 165, 233, 0.3);
		}

		* { box-sizing: border-box; font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; }
		body { background: linear-gradient(135deg, var(--bg-primary) 0%, #1e1b4b 100%); color: var(--text); min-height: 100vh; }

		.bg-clouds { position: fixed; inset: 0; overflow: hidden; z-index: -1; }
		.cloud { position: absolute; background: linear-gradient(135deg, rgba(255,255,255,.1), rgba(255,255,255,.05)); border-radius: 50px; opacity: .25; animation: float 22s linear infinite; }
		.cloud:nth-child(1){ width:120px;height:60px;top:20%;left:-140px;animation-duration:26s;}
		.cloud:nth-child(2){ width:160px;height:80px;top:60%;left:-180px;animation-duration:30s;animation-delay:-10s;}
		.cloud:nth-child(3){ width:90px;height:45px;top:80%;left:-100px;animation-duration:34s;animation-delay:-18s;}
		@keyframes float{from{transform:translateX(0)}to{transform:translateX(calc(100vw + 220px))}}

		.glass-card { background: var(--bg-glass); backdrop-filter: blur(18px); border: 1px solid rgba(255,255,255,0.1); box-shadow: var(--shadow); transition: .25s ease; }
		.glass-card:hover { background: var(--bg-glass-light); box-shadow: var(--shadow-glow); }
		.glass-input { background: rgba(30,41,59,.45); border: 1px solid rgba(255,255,255,.08); color: var(--text); transition: .2s; }
		.glass-input::placeholder { color: var(--text-secondary); }
		.glass-input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(14,165,233,.12); background: rgba(30,41,59,.6); }
		.glass-button { background: linear-gradient(135deg, var(--primary), var(--secondary)); color:#fff; box-shadow: var(--shadow); transition: transform .15s ease; }
		.glass-button:hover { transform: translateY(-1px); box-shadow: var(--shadow-glow); }

		/* Fixed Icon centering */
		.input-container {
			position: relative;
		}
		.input-icon {
			position: absolute;
			left: 0;
			top: 0;
			height: 100%;
			display: flex;
			align-items: center;
			padding-left: 0.75rem;
			pointer-events: none;
		}
		.input-icon svg { 
			display: block; 
			width: 20px; 
			height: 20px; 
			color: var(--text-secondary); 
		}
		.input-with-icon {
			padding-left: 3rem; /* Increased from 1rem to 3rem to accommodate icon */
		}

		/* Autofill fix */
		input:-webkit-autofill,
		input:-webkit-autofill:hover,
		input:-webkit-autofill:focus { 
			-webkit-text-fill-color: #f8fafc; 
			transition: background-color 9999s ease-in-out 0s; 
		}

		/* small screens */
		@media (max-width: 640px){ 
			.glass-card{ margin: 0 1rem; padding: 1.25rem; } 
		}
	</style>
</head>
<body class="min-h-screen flex items-center justify-center p-6">
	<div class="bg-clouds">
		<div class="cloud"></div>
		<div class="cloud"></div>
		<div class="cloud"></div>
	</div>

	<div class="w-full max-w-md">
		<div class="text-center mb-6">
			<div class="inline-flex items-center justify-center w-16 h-16 rounded-full" style="background: linear-gradient(135deg, var(--primary), var(--secondary)); box-shadow: var(--shadow-glow);">
				<svg class="w-8 h-8 text-white block" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2m12-10a4 4 0 100-8 4 4 0 000 8z"/></svg>
			</div>
			<h1 class="text-2xl font-semibold mt-3">Create Account</h1>
			<p class="text-sm" style="color: var(--text-secondary);">Join Persona and take control of your finances</p>
		</div>

		<div class="glass-card rounded-2xl p-6">
			<form method="POST" action="{{ route('register') }}" class="space-y-4">
				@csrf

				<!-- Full Name -->
				<div>
					<label for="name" class="block text-sm mb-1.5" style="color: var(--text-secondary);">Full Name</label>
					<div class="input-container">
						<div class="input-icon">
							<!-- user icon -->
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A9 9 0 0112 15a9 9 0 016.879 2.804M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
						</div>

						<input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="name"
							class="glass-input input-with-icon w-full pr-4 h-12 rounded-lg" placeholder="Enter your full name">
					</div>
					@error('name')<p class="mt-2 text-sm error-message p-2 rounded" style="color: #ffaeb0;">{{ $message }}</p>@enderror
				</div>

				<!-- Email -->
				<div>
					<label for="email" class="block text-sm mb-1.5" style="color: var(--text-secondary);">Email Address</label>
					<div class="input-container">
						<div class="input-icon">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
						</div>
						<input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="username"
							class="glass-input input-with-icon w-full pr-4 h-12 rounded-lg" placeholder="you@example.com">
					</div>
					@error('email')<p class="mt-2 text-sm error-message p-2 rounded" style="color: #ffaeb0;">{{ $message }}</p>@enderror
				</div>

				<!-- Password -->
				<div>
					<label for="password" class="block text-sm mb-1.5" style="color: var(--text-secondary);">Password</label>
					<div class="input-container">
						<div class="input-icon">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
						</div>
						<input id="password" name="password" type="password" required autocomplete="new-password"
							class="glass-input input-with-icon w-full pr-4 h-12 rounded-lg" placeholder="Create a strong password">
					</div>
					@error('password')<p class="mt-2 text-sm error-message p-2 rounded" style="color: #ffaeb0;">{{ $message }}</p>@enderror
				</div>

				<!-- Confirm Password -->
				<div>
					<label for="password_confirmation" class="block text-sm mb-1.5" style="color: var(--text-secondary);">Confirm Password</label>
					<div class="input-container">
						<div class="input-icon">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
						</div>
						<input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
							class="glass-input input-with-icon w-full pr-4 h-12 rounded-lg" placeholder="Confirm your password">
					</div>
				</div>

				<!-- Terms / Optional checkbox example -->
				<div class="flex items-center space-x-3">
					<label class="inline-flex items-center space-x-2">
						<input type="checkbox" name="terms" class="rounded border-slate-600 h-4 w-4 align-middle">
						<span class="text-sm" style="color: var(--text-secondary);">Agree to terms</span>
					</label>
				</div>

				<button type="submit" class="glass-button w-full py-3 rounded-lg font-semibold">Create Account</button>

				<p class="text-center text-sm" style="color: var(--text-secondary);">
					Already have an account? <a href="{{ route('login') }}" style="color: var(--primary);">Sign in</a>
				</p>
			</form>
		</div>
	</div>

	<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>