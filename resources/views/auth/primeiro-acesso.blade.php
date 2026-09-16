<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Primeiro Acesso — Conselheiros Municipais</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="min-h-screen bg-gray-50 flex flex-col">

    {{-- Header --}}
    <header class="bg-white border-b border-gray-200 px-6 py-4">
        <div class="max-w-lg mx-auto flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center text-white font-bold text-sm">CM</div>
            <span class="font-semibold text-gray-800">Conselhos Municipais</span>
        </div>
    </header>

    <main class="flex-1 flex items-center justify-center px-4 py-12">
        <div class="w-full max-w-md">

            {{-- Sucesso --}}
            @if (session('sucesso'))
                <div class="mb-6 rounded-xl bg-green-50 border border-green-200 p-5 text-center">
                    <div class="text-3xl mb-2">✉️</div>
                    <p class="font-semibold text-green-800 mb-1">Verifique seu e-mail</p>
                    <p class="text-green-700 text-sm">{{ session('sucesso') }}</p>
                    <a href="/painel/login" class="mt-4 inline-block text-sm text-blue-600 hover:underline">Ir para o login</a>
                </div>

            {{-- Formulário: pedir e-mail do conselheiro sem e-mail cadastrado --}}
            @elseif (session('pedir_email'))
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
                    <div class="text-center mb-6">
                        <div class="w-14 h-14 rounded-full bg-blue-100 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-7 h-7 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                            </svg>
                        </div>
                        <h1 class="text-xl font-bold text-gray-900">Cadastrar e-mail</h1>
                        <p class="text-gray-500 text-sm mt-1">Olá, <strong>{{ session('conselheiro_nome') }}</strong>! Informe seu e-mail para receber o link de acesso.</p>
                    </div>

                    <form method="POST" action="{{ route('auth.primeiro-acesso.email') }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="conselheiro_id" value="{{ session('conselheiro_id') }}">

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">E-mail</label>
                            <input type="email" name="email" value="{{ old('email') }}" required
                                   class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('email') border-red-400 @enderror"
                                   placeholder="seu@email.com">
                            @error('email')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-4 rounded-lg text-sm transition-colors">
                            Cadastrar e-mail e receber link de acesso
                        </button>
                    </form>
                </div>

            {{-- Formulário principal: identificar conselheiro --}}
            @else
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
                    <div class="text-center mb-6">
                        <div class="w-14 h-14 rounded-full bg-blue-100 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-7 h-7 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h1 class="text-xl font-bold text-gray-900">Primeiro acesso</h1>
                        <p class="text-gray-500 text-sm mt-1">Sou conselheiro e quero acessar o sistema</p>
                    </div>

                    @if ($errors->any())
                        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 p-3 text-sm text-red-700">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('auth.primeiro-acesso') }}" class="space-y-4">
                        @csrf

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">E-mail ou CPF cadastrado</label>
                            <input type="text" name="identificador" value="{{ old('identificador') }}" required autofocus
                                   class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="seu@email.com ou 000.000.000-00">
                            <p class="mt-1 text-xs text-gray-400">Informe o e-mail ou CPF registrado quando você foi nomeado(a) como conselheiro(a).</p>
                        </div>

                        <button type="submit"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-4 rounded-lg text-sm transition-colors">
                            Verificar e receber link de acesso
                        </button>
                    </form>

                    <div class="mt-6 pt-5 border-t border-gray-100 text-center space-y-2">
                        <a href="/painel/login" class="block text-sm text-blue-600 hover:underline">
                            Já tenho cadastro — Fazer login
                        </a>
                        <a href="/painel/password-reset/request" class="block text-sm text-gray-500 hover:underline">
                            Esqueci minha senha
                        </a>
                    </div>
                </div>

                <p class="mt-6 text-center text-xs text-gray-400">
                    Problema com o acesso? Entre em contato com o administrador do seu município.
                </p>
            @endif
        </div>
    </main>

</body>
</html>
