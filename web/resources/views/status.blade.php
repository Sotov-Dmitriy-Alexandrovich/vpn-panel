@extends('layouts.app')

@section('content')
    <div class="status-page">
        <h1 class="page-title">📊 Статус сервера</h1>

        <div class="status-grid" id="status-container">
            <!-- CPU -->
            <div class="status-card">
                <div class="card-header">
                    <span class="card-icon">🔥</span>
                    <span class="card-title">CPU</span>
                </div>
                <div class="card-content">
                    <div class="progress-container">
                        <div class="progress-bar">
                            <div class="progress-fill" id="cpu-bar" style="width: {{ $data['cpu']['usage'] }}%"></div>
                        </div>
                        <span class="progress-text" id="cpu-text">{{ $data['cpu']['usage'] }}%</span>
                    </div>
                    <div class="card-details">
                        <div class="detail-item"><span>Ядер:</span><span id="cpu-cores">{{ $data['cpu']['cores'] }}</span></div>
                        <div class="detail-item"><span>Load (1m):</span><span id="cpu-load">{{ $data['cpu']['load_1min'] }}</span></div>
                    </div>
                </div>
            </div>

            <!-- RAM -->
            <div class="status-card">
                <div class="card-header">
                    <span class="card-icon">💾</span>
                    <span class="card-title">RAM</span>
                </div>
                <div class="card-content">
                    <div class="progress-container">
                        <div class="progress-bar">
                            <div class="progress-fill" id="ram-bar" style="width: {{ $data['ram']['usage'] }}%"></div>
                        </div>
                        <span class="progress-text" id="ram-text">{{ $data['ram']['usage'] }}%</span>
                    </div>
                    <div class="card-details">
                        <div class="detail-item"><span>Всего:</span><span>{{ $data['ram']['total'] }} MB</span></div>
                        <div class="detail-item"><span>Свободно:</span><span id="ram-free">{{ $data['ram']['free'] }} MB</span></div>
                    </div>
                </div>
            </div>

            <!-- Disk -->
            <div class="status-card">
                <div class="card-header">
                    <span class="card-icon">💿</span>
                    <span class="card-title">Диск (/)</span>
                </div>
                <div class="card-content">
                    <div class="progress-container">
                        <div class="progress-bar">
                            <div class="progress-fill" id="disk-bar" style="width: {{ $data['disk']['usage_percent'] }}%"></div>
                        </div>
                        <span class="progress-text" id="disk-text">{{ $data['disk']['usage_percent'] }}%</span>
                    </div>
                    <div class="card-details">
                        <div class="detail-item"><span>Всего:</span><span>{{ $data['disk']['size'] }}</span></div>
                        <div class="detail-item"><span>Свободно:</span><span id="disk-free">{{ $data['disk']['available'] }}</span></div>
                    </div>
                </div>
            </div>

            <!-- Uptime -->
            <div class="status-card">
                <div class="card-header">
                    <span class="card-icon">⏱️</span>
                    <span class="card-title">Uptime</span>
                </div>
                <div class="card-content">
                    <div class="uptime-value" id="uptime-text">{{ $data['uptime'] }}</div>
                    <div class="card-details">
                        <div class="detail-item"><span>Load (5m):</span><span id="load-5m">{{ $data['load']['5min'] }}</span></div>
                        <div class="detail-item"><span>Load (15m):</span><span id="load-15m">{{ $data['load']['15min'] }}</span></div>
                    </div>
                </div>
            </div>

            <!-- Network -->
            <div class="status-card full-width">
                <div class="card-header">
                    <span class="card-icon">🌐</span>
                    <span class="card-title">Сетевые интерфейсы</span>
                </div>
                <div class="card-content">
                    <div class="network-grid" id="network-grid">
                        @foreach($data['network'] as $interface)
                            <div class="network-item">
                                <div class="network-name">{{ $interface['name'] }}</div>
                                <div class="network-stats">
                                    <div class="network-stat">
                                        <span class="stat-label">↓ RX:</span>
                                        <span class="stat-value" data-iface="{{ $interface['name'] }}" data-type="rx">{{ $interface['rx'] }}</span>
                                    </div>
                                    <div class="network-stat">
                                        <span class="stat-label">↑ TX:</span>
                                        <span class="stat-value" data-iface="{{ $interface['name'] }}" data-type="tx">{{ $interface['tx'] }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Функция для обновления данных в реальном времени каждые 5 секунд
        function updateStatus() {
            fetch(window.location.href, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(response => response.json())
                .then(data => {
                    // CPU
                    document.getElementById('cpu-bar').style.width = data.cpu.usage + '%';
                    document.getElementById('cpu-text').innerText = data.cpu.usage + '%';
                    document.getElementById('cpu-load').innerText = data.cpu.load_1min;

                    // RAM
                    document.getElementById('ram-bar').style.width = data.ram.usage + '%';
                    document.getElementById('ram-text').innerText = data.ram.usage + '%';
                    document.getElementById('ram-free').innerText = data.ram.free + ' MB';

                    // Disk
                    document.getElementById('disk-bar').style.width = data.disk.usage_percent + '%';
                    document.getElementById('disk-text').innerText = data.disk.usage_percent + '%';
                    document.getElementById('disk-free').innerText = data.disk.available;

                    // Uptime & Load
                    document.getElementById('uptime-text').innerText = data.uptime;
                    document.getElementById('load-5m').innerText = data.load['5min'];
                    document.getElementById('load-15m').innerText = data.load['15min'];

                    // Network
                    data.network.forEach(net => {
                        const rxEl = document.querySelector(`.stat-value[data-iface="${net.name}"][data-type="rx"]`);
                        const txEl = document.querySelector(`.stat-value[data-iface="${net.name}"][data-type="tx"]`);
                        if (rxEl) rxEl.innerText = net.rx;
                        if (txEl) txEl.innerText = net.tx;
                    });
                })
                .catch(err => console.error('Ошибка обновления статуса:', err));
        }

        // Запускаем обновление каждые 5 секунд
        setInterval(updateStatus, 5000);
    </script>
@endsection
