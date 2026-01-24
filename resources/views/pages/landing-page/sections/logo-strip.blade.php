<section class="py-20 bg-gray-100/30">
    <div class="container mx-auto px-6">
        <h2 class="text-center text-2xl md:text-3xl font-bold text-gray-900 mb-3">
            Suporte a diversos{" "}
            <span class="text-gray-900">bancos de dados</span>
        </h2>
        <p class="text-center text-gray-600 mb-12 max-w-2xl mx-auto">
            Compatível com os principais bancos relacionais e NoSQL do mercado
        </p>
        <div class="flex flex-wrap items-center justify-center gap-6 md:gap-10">
            @php
                $databases = [
                    ['name' => 'MySQL', 'icon' => '🐬'],
                    ['name' => 'PostgreSQL', 'icon' => '🐘'],
                    ['name' => 'SQL Server', 'icon' => '⚡'],
                    ['name' => 'SQLite', 'icon' => '📦'],
                    ['name' => 'Redis', 'icon' => '🔴'],
                    ['name' => 'MariaDB', 'icon' => '🦭'],
                    ['name' => 'MongoDB', 'icon' => '🍃'],
                    ['name' => 'Oracle', 'icon' => '⭕'],
                    ['name' => 'Cassandra', 'icon' => '👁️'],
                    ['name' => 'ClickHouse', 'icon' => '🏠'],
                ];
            @endphp

            @foreach($databases as $db)
                <div class="flex items-center gap-2.5 px-4 py-2.5 rounded-xl bg-white border border-gray-200/50 shadow-sm hover:shadow-md hover:border-gray-200 transition-all duration-300">
                    <span class="text-xl">{{ $db['icon'] }}</span>
                    <span class="text-sm font-semibold text-gray-900">
                        {{ $db['name'] }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>
</section>
