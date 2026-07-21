@extends('layouts.app')

@section('content')
    <div class="add-client-page">
        <h2 class="page-title">Добавить пользователя</h2>

        <form id="add-form" class="add-form">
            @csrf
            <input type="text" id="client-name" name="client_name" placeholder="Имя пользователя (латиницей, без пробелов)" required pattern="[a-zA-Z0-9_-]+">
            <button type="submit" id="submit-btn">Сгенерировать конфиг</button>
        </form>

        <div id="result-container" class="result-container" style="display: none;">
            <p id="add-result" class="success-text"></p>

            <div class="qr-section">
                <p class="qr-hint">📱 Отсканируйте QR-код камерой телефона для скачивания:</p>
                <div id="qrcode" class="qr-box"></div>
                <a id="download-link" class="btn-download" href="#" target="_blank">
                    ⬇️ Скачать .ovpn файл напрямую
                </a>
            </div>
        </div>
    </div>

    <!-- Подключаем легкую библиотеку для генерации QR-кодов -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('add-form');
            const resultContainer = document.getElementById('result-container');
            const resultText = document.getElementById('add-result');
            const qrContainer = document.getElementById('qrcode');
            const downloadLink = document.getElementById('download-link');
            let qrCodeObj = null;

            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                const clientName = document.getElementById('client-name').value.trim();
                const submitBtn = document.getElementById('submit-btn');

                if (!clientName) return;

                // Блокируем кнопку на время генерации
                submitBtn.disabled = true;
                submitBtn.innerText = 'Генерация...';
                resultContainer.style.display = 'none';
                qrContainer.innerHTML = ''; // Очищаем старый QR, если был

                try {
                    const response = await fetch('/add', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ client_name: clientName })
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        resultText.innerText = `✅ Пользователь "${clientName}" успешно создан!`;
                        resultText.className = 'success-text';
                        resultContainer.style.display = 'block';

                        // Формируем ссылку на скачивание (маршрут /download/имя.ovpn)
                        const downloadUrl = `/download/${clientName}.ovpn`;
                        downloadLink.href = downloadUrl;

                        // Генерируем QR-код
                        qrCodeObj = new QRCode(qrContainer, {
                            text: downloadUrl,
                            width: 200,
                            height: 200,
                            colorDark: "#d25afa", // Фиолетовый цвет под стиль твоего сайта
                            colorLight: "#ffffff",
                            correctLevel: QRCode.CorrectLevel.H
                        });
                    } else {
                        resultText.innerText = data.message || '❌ Ошибка при создании пользователя';
                        resultText.className = 'error-text';
                        resultContainer.style.display = 'block';
                    }
                } catch (error) {
                    console.error(error);
                    resultText.innerText = '❌ Ошибка сети. Попробуйте позже.';
                    resultText.className = 'error-text';
                    resultContainer.style.display = 'block';
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.innerText = 'Сгенерировать конфиг';
                }
            });
        });
    </script>
@endsection
