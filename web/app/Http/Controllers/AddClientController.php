<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

class AddClientController extends Controller
{
    public function store(Request $request)
    {
        // 1. Валидация имени
        $request->validate([
            'client_name' => 'required|string|regex:/^[a-zA-Z0-9_-]+$/',
        ]);

        $clientName = $request->input('client_name');
        $filename = $clientName . '.ovpn';

        // Путь, куда скрипт сохраняет конфиги
        $filePath = "/opt/vpn-panel/www/downloads/{$filename}";

        // 2. Проверяем, нет ли уже такого файла
        if (File::exists($filePath)) {
            return response()->json([
                'success' => false,
                'message' => 'Пользователь с таким именем уже существует!'
            ], 409);
        }

        // 3. Вызываем твой скрипт генерации
        // ⚠️ ВАЖНО: Укажи правильный путь к скрипту!
        // Если скрипт называется по-другому или лежит в другой папке, измени путь
        $scriptPath = '/opt/vpn-panel/scripts/create-client.sh';

        if (!File::exists($scriptPath)) {
            return response()->json([
                'success' => false,
                'message' => "Скрипт генерации не найден по пути: {$scriptPath}"
            ], 500);
        }

        try {
            // Выполняем скрипт с передачей имени клиента как аргумента
            $result = Process::timeout(30)->run("sudo {$scriptPath} {$clientName}");

            if ($result->successful() && str_contains($result->output(), 'SUCCESS')) {
                // Проверяем, действительно ли файл создался
                if (File::exists($filePath)) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Конфиг успешно создан',
                        'filename' => $filename
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Скрипт выполнен, но файл не найден. Проверьте логи.'
                    ], 500);
                }
            } else {
                $errorOutput = $result->errorOutput() ?: $result->output();
                return response()->json([
                    'success' => false,
                    'message' => 'Ошибка генерации: ' . $errorOutput
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Исключение: ' . $e->getMessage()
            ], 500);
        }
    }
}
