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

    <header class="bg-white border-b border-gray-200 px-6 py-4">
        <div class="max-w-lg mx-auto flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center text-white font-bold text-sm">CM</div>
            <span class="font-semibold text-gray-800">Conselhos Municipais</span>
        </div>
    </header>

    <main class="flex-1 flex items-center justify-center px-4 py-12">
        <div class="w-full max-w-md">

            @if (session('sucesso'))
                <div class="mb-6 rounded-xl bg-green-50 border border-green-200 p-6 text-center">
                    <div class="text-4xl mb-3">✉️</div>
                    <p class="font-semibold text-green-800 mb-2">Verifique seu e-mail</p>
                    <p class="text-green-700 text-sm">{{ session('sucesso') }}</p>
                    <p class="text-gray-500 text-xs mt-3">Não recebeu? Verifique a pasta de spam ou entre em contato com o administrador do seu município.</p>
                    <a href="/painel/login" class="mt-4 inline-block text-sm text-blue-600 hover:underline font-medium">Ir para o login →</a>
                </div>
            @else
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
                    <div class="text-center mb-6">
                        <div class="w-14 h-14 rounded-full bg-blue-100 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-7 h-7 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h1 class="text-xl font-bold text-gray-900">Primeiro acesso</h1>
                        <p class="text-gray-500 text-sm mt-1">Sou conselheiro e quero criar minha senha de acesso</p>
                    </div>

                    @if ($errors->any())
                        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 p-3 text-sm text-red-700">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('auth.primeiro-acesso.store') }}" class="space-y-4">
                        @csrf

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                E-mail cadastrado pelo administrador
                            </label>
                            <input type="email" name="email" value="{{ old('email') }}"
                                   required autofocus autocomplete="email"
                                   class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="seu@email.com">
                            <p class="mt-1.5 text-xs text-gray-400">
                                Use o e-mail que o administrador municipal registrou no seu cadastro de conselheiro.
                            </p>
                        </div>

                        <button type="submit"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-4 rounded-lg text-sm transition-colors">
                            Receber link de acesso
                        </button>
                    </form>

                    <div class="mt-6 pt-5 border-t border-gray-100 text-center space-y-2">
                        <a href="/painel/login" class="block text-sm text-blue-600 hover:underline">
                            Já tenho senha — Fazer login
                        </a>
                        <a href="/painel/password-reset/request" class="block text-sm text-gray-500 hover:underline">
                            Esqueci minha senha
                        </a>
                    </div>
                </div>

                <p class="mt-5 text-center text-xs text-gray-400">
                    E-mail não cadastrado? Entre em contato com o administrador do seu município para que ele registre seu e-mail no sistema.
                </p>
            @endif

        </div>
    </main>

</body>
</html>
