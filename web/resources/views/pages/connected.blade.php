@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-4xl font-bold text-gray-900">Подключённые клиенты</h1>
        <div id="last-update" class="text-sm text-gray-500 font-medium"></div>
    </div>

    <div id="clients-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Здесь будут карточки -->
    </div>

    <div id="no-clients" class="hidden text-center py-20 bg-gray-50 rounded-3xl">
    </div>
</div>

<script>
function loadClients() {
    fetch('/api/clients')
        .then(r => r.json())
        .then(data => {
            const container = document.getElementById('clients-container');
            const noClients = document.getElementById('no-clients');

            container.innerHTML = '';

            if (!data.clients || data.clients.length === 0) {
                noClients.classList.remove('hidden');
                document.getElementById('last-update').textContent = 'Обновлено: ' + new Date().toLocaleTimeString('ru-RU');
                return;
            }

            noClients.classList.add('hidden');

            data.clients.forEach(client => {
                const card = document.createElement('div');
                card.className = `bg-white rounded-3xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-all`;
                card.innerHTML = `
                    <div class="flex items-center gap-4">
                        <div class="w-5 h-5 ${client.color} rounded-full ring-4 ring-green-100"></div>
                        <div class="flex-1">
                            <div class="text-xl font-semibold text-gray-900">${client.name}</div>
                            <div class="text-sm text-gray-500">${client.real_ip}</div>
                        </div>
                    </div>

                    <div class="mt-6 pt-6 border-t grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500">Отправлено</span><br>
                            <span class="font-medium">${client.out}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Получено</span><br>
                            <span class="font-medium">${client.in}</span>
                        </div>
                    </div>

                    <div class="mt-4 text-xs text-gray-400">
                        Подключён: ${client.since}
                    </div>
                `;
                container.appendChild(card);
            });

            document.getElementById('last-update').textContent = 'Обновлено: ' + new Date().toLocaleTimeString('ru-RU');
        })
        .catch(() => {
            console.log('Ошибка загрузки');
        });
}

// Первый запуск + обновление каждые 3 секунды
loadClients();
setInterval(loadClients, 3000);
</script>
@endsection

<script>
loadClients();
setInterval(loadClients, 10000);
</script>
