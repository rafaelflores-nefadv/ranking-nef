@extends('layouts.app')

@section('title', 'Landing Game League')

@section('content')
<div class="min-h-screen bg-[#020617] text-white">
    <div class="max-w-7xl mx-auto px-4 py-12 space-y-12">
        <!-- HERO -->
        <section class="grid lg:grid-cols-2 gap-8 items-center">
            <div class="space-y-6">
                <p class="text-sm uppercase tracking-[0.4em] text-slate-400">Landing Page</p>
                <h1 class="text-4xl lg:text-5xl font-semibold leading-tight">
                    Transforme metas em competição.<br>
                    Exiba resultados com a identidade da sua empresa.
                </h1>
                <p class="text-lg text-slate-300 max-w-xl">
                    Monitores de ranking e jogos corporativos totalmente personalizáveis para TVs, dashboards e campanhas internas.
                    Nada genérico. Tudo com a sua marca.
                </p>
                <div class="flex flex-wrap gap-3">
                    <button class="px-6 py-3 rounded-full font-semibold text-white transition-all duration-200 shadow-xl" style="background: linear-gradient(90deg, #1e40af, #2563eb, rgb(243, 138, 39), rgba(243, 119, 53, 0.95));">
                        👉 Ver com a minha marca
                    </button>
                    <button class="px-6 py-3 rounded-full border border-white/40 text-white hover:border-white transition-all duration-200">
                        👉 Solicitar demonstração
                    </button>
                </div>
                <p class="text-sm text-slate-400 max-w-md">
                    Cada empresa possui seu próprio ambiente, visual e jogos exclusivos.
                </p>
            </div>
            <div class="p-6 bg-slate-900/60 rounded-3xl shadow-2xl border border-slate-700/60">
                <div class="mb-4 font-semibold">Monitor em tempo real</div>
                <div class="h-96 bg-gradient-to-br from-slate-900 to-slate-950 rounded-2xl border border-slate-700 p-6 flex flex-col justify-between">
                    <div class="space-y-2">
                        <p class="text-slate-400 text-sm">Mockup</p>
                        <h3 class="text-xl font-semibold">Ranking TV</h3>
                        <div class="flex items-center gap-3 text-sm">
                            <span class="px-3 py-1 bg-white/10 rounded-full">Trocar empresa</span>
                            <span class="px-3 py-1 bg-white/10 rounded-full">Trocar logo</span>
                            <span class="px-3 py-1 bg-white/10 rounded-full">Trocar cores</span>
                        </div>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Legenda</p>
                        <p class="text-sm text-slate-300">O mesmo sistema. Experiências totalmente diferentes.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- PROBLEM -->
        <section class="bg-slate-900/50 border border-slate-800 rounded-3xl p-8 space-y-4">
            <h2 class="text-3xl font-semibold">Planilhas e rankings genéricos não engajam ninguém.</h2>
            <p class="text-slate-300 max-w-3xl">
                Metas escondidas, dados estáticos e ferramentas sem identidade visual não motivam equipes.
                Quando tudo parece igual, ninguém se importa.
            </p>
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach(['Visual genérico','Dados sem impacto','TVs corporativas subutilizadas','Pouco engajamento do time'] as $item)
                    <div class="bg-[#0f172a]/70 border border-slate-800 rounded-2xl p-4 text-sm">
                        ❌ {{ $item }}
                    </div>
                @endforeach
            </div>
        </section>

        <!-- SOLUTION -->
        <section class="space-y-4">
            <h2 class="text-3xl font-semibold">Um ranking que se adapta à sua empresa — não o contrário.</h2>
            <p class="text-slate-300 max-w-3xl">
                Criamos uma plataforma onde cada empresa possui seu próprio ambiente, com identidade visual, jogos e monitores personalizados.
                Você escolhe como exibir seus resultados. Nós cuidamos do resto.
            </p>
        </section>

        <!-- PERSONALIZATION -->
        <section class="space-y-6">
            <div class="flex items-center justify-between">
                <h2 class="text-3xl font-semibold">Sua marca em cada detalhe.</h2>
                <span class="text-sm text-slate-400">Tudo configurável. Sem código.</span>
            </div>
            <div class="grid md:grid-cols-3 gap-6">
                <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 space-y-3">
                    <h3 class="text-xl font-semibold text-white">Identidade da Empresa</h3>
                    <ul class="space-y-2 text-slate-300 text-sm">
                        <li>Logo</li>
                        <li>Cores</li>
                        <li>Tema visual</li>
                        <li>Estilo corporativo</li>
                    </ul>
                </div>
                <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 space-y-3">
                    <h3 class="text-xl font-semibold text-white">Jogo & Ranking</h3>
                    <ul class="space-y-2 text-slate-300 text-sm">
                        <li>Nome do jogo</li>
                        <li>Regras próprias</li>
                        <li>Pódio personalizado</li>
                        <li>Animações e destaques</li>
                    </ul>
                </div>
                <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 space-y-3">
                    <h3 class="text-xl font-semibold text-white">Monitor</h3>
                    <ul class="space-y-2 text-slate-300 text-sm">
                        <li>Layout para TV ou dashboard</li>
                        <li>Fonte ajustada para telão</li>
                        <li>Background customizado</li>
                        <li>Animações ativadas ou não</li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- BEFORE AFTER -->
        <section class="bg-slate-900/50 border border-slate-800 rounded-3xl p-8 grid md:grid-cols-2 gap-8">
            <div>
                <p class="text-sm uppercase tracking-[0.4em] text-slate-500">Antes</p>
                <ul class="space-y-2 text-slate-300 text-lg">
                    <li>Ranking padrão</li>
                    <li>Visual neutro</li>
                    <li>Nenhuma identidade</li>
                </ul>
            </div>
            <div>
                <p class="text-sm uppercase tracking-[0.4em] text-slate-500">Depois</p>
                <ul class="space-y-2 text-white text-lg">
                    <li>Logo da empresa</li>
                    <li>Cores da marca</li>
                    <li>Nome do jogo personalizado</li>
                    <li>Experiência visual envolvente</li>
                </ul>
                <p class="mt-4 text-sm text-slate-400">O mesmo ranking. Uma experiência completamente diferente.</p>
            </div>
        </section>

        <!-- ORGANIZATION -->
        <section class="space-y-4">
            <h2 class="text-3xl font-semibold">Criado para equipes, setores e campanhas.</h2>
            <p class="text-slate-300 max-w-3xl">
                Organize seus rankings por setor, equipe ou objetivo. Cada monitor exibe exatamente o que faz sentido para aquele ambiente.
            </p>
            <div class="grid md:grid-cols-3 gap-4 text-sm">
                @foreach(['Vendas','Atendimento','Cobrança','Operações','RH e Cultura','Campanhas internas'] as $item)
                    <div class="bg-[#0f172a]/60 border border-slate-800 rounded-2xl p-4 text-slate-300">
                        {{ $item }}
                    </div>
                @endforeach
            </div>
        </section>

        <!-- REAL-TIME ENGAGEMENT -->
        <section class="bg-slate-900/60 border border-slate-800 rounded-3xl p-8 space-y-4">
            <h2 class="text-3xl font-semibold">Engajamento em tempo real</h2>
            <div class="grid md:grid-cols-4 gap-4 text-sm">
                @foreach(['Atualização automática','Destaques de desempenho','Animações de pódio','Notificações visuais','Sons e leitura por voz'] as $item)
                    <div class="bg-[#020617]/70 border border-slate-800 rounded-2xl p-4 text-slate-300">
                        {{ $item }}
                    </div>
                @endforeach
            </div>
            <p class="text-slate-400">Transforme números em experiência.</p>
        </section>

        <!-- WHERE USE -->
        <section class="space-y-4">
            <h2 class="text-3xl font-semibold">Funciona em qualquer tela.</h2>
            <div class="grid md:grid-cols-3 gap-4">
                @foreach(['TVs corporativas','Dashboards internos','Links públicos','Eventos e campanhas','Telões de operação'] as $item)
                    <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-4 text-slate-300 text-sm">
                        {{ $item }}
                    </div>
                @endforeach
            </div>
        </section>

        <!-- SIMPLE SETUP -->
        <section class="space-y-4">
            <h2 class="text-3xl font-semibold">Do cadastro à TV em poucos passos.</h2>
            <div class="grid md:grid-cols-4 gap-4">
                @foreach(['Cadastre sua empresa','Personalize identidade e jogo','Crie seus monitores','Exiba em qualquer tela'] as $item)
                    <div class="bg-[#0f172a]/70 border border-slate-800 rounded-2xl p-4 text-sm text-slate-300">
                        {{ $item }}
                    </div>
                @endforeach
            </div>
            <p class="text-slate-400">Sem código. Sem complexidade.</p>
        </section>

        <!-- SECURITY -->
        <section class="bg-slate-900/50 border border-slate-800 rounded-3xl p-8 space-y-4">
            <h2 class="text-3xl font-semibold">Seu ambiente é só seu.</h2>
            <div class="grid md:grid-cols-3 gap-4 text-sm">
                @foreach(['Ambiente exclusivo por empresa','Dados isolados','Controle de acesso por equipe','Total segurança'] as $item)
                    <div class="bg-[#020617]/60 border border-slate-800 rounded-2xl p-4 text-slate-300">
                        {{ $item }}
                    </div>
                @endforeach
            </div>
        </section>

        <!-- FOR WHOM -->
        <section class="space-y-4">
            <h2 class="text-3xl font-semibold">Para quem é</h2>
            <div class="grid md:grid-cols-5 gap-4 text-sm">
                @foreach(['Gestores','Coordenadores','Supervisores','RH e Cultura','Operações e Vendas'] as $item)
                    <div class="bg-slate-900/40 border border-slate-800 rounded-2xl p-4 text-slate-300">
                        {{ $item }}
                    </div>
                @endforeach
            </div>
            <p class="text-slate-400">Se você acompanha metas, essa plataforma é pra você.</p>
        </section>

        <!-- FINAL CTA -->
        <section class="bg-gradient-to-br from-[#020617] to-slate-900 border border-slate-800 rounded-3xl p-8 text-center space-y-4">
            <h2 class="text-3xl font-semibold">Veja seus resultados com a sua identidade.</h2>
            <p class="text-slate-300 max-w-2xl mx-auto">
                Solicite uma demonstração personalizada e veja seu ranking com logo, cores e visual da sua empresa.
            </p>
            <div class="flex flex-wrap justify-center gap-4">
                <button class="px-6 py-3 rounded-full font-semibold text-white transition-all duration-200" style="background: linear-gradient(90deg, #1e40af, #2563eb, rgb(243, 138, 39), rgba(243, 119, 53, 0.95));">
                    👉 Ver com a minha marca
                </button>
                <button class="px-6 py-3 rounded-full border border-white/50 text-white transition-all duration-200">
                    👉 Solicitar demonstração
                </button>
            </div>
        </section>
    </div>
</div>
@endsection
