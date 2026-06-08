<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Поиск по сайту — LVT Market</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; color: #1f2937; }
        h1 { margin-bottom: 16px; }
        form { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr auto; gap: 12px; margin-bottom: 16px; align-items: end; }
        label { display: block; font-size: 13px; margin-bottom: 4px; color: #4b5563; }
        input, select, button { width: 100%; padding: 9px 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; }
        button { background: #15803d; color: #fff; border: none; cursor: pointer; }
        button:disabled { opacity: 0.7; cursor: wait; }
        .meta { margin-bottom: 12px; color: #4b5563; font-size: 14px; }
        .alert { background: #fff7ed; border: 1px solid #fdba74; padding: 10px 12px; border-radius: 6px; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th, td { border-bottom: 1px solid #e5e7eb; padding: 8px; text-align: left; vertical-align: top; }
        th { background: #f9fafb; }
        .pagination { margin-top: 14px; }
        .timings { margin-top: 10px; font-size: 12px; color: #6b7280; }
        a { color: #15803d; }
    </style>
</head>
<body>
    <h1>Поиск по сайту</h1>
    <p>Поиск по каталогу <strong>lvtec.ru</strong> с автоматическим fallback в <strong>Mouser</strong>.</p>

    <form method="GET" action="{{ route('search.index') }}" id="search-form">
        <div>
            <label for="componentNum">Поиск по сайту</label>
            <input id="componentNum" name="componentNum" type="text" value="{{ $componentNum }}" placeholder="Артикул или наименование" required>
        </div>
        <div>
            <label for="amount">Количество</label>
            <input id="amount" name="amount" type="number" min="1" value="{{ $amount }}">
        </div>
        <div>
            <label for="sort">Сортировка</label>
            <select id="sort" name="sort">
                <option value="price_asc" @selected($sort === 'price_asc')>Цена по возрастанию</option>
                <option value="price_desc" @selected($sort === 'price_desc')>Цена по убыванию</option>
                <option value="lead_asc" @selected($sort === 'lead_asc')>Срок поставки</option>
                <option value="stock_desc" @selected($sort === 'stock_desc')>Наличие</option>
            </select>
        </div>
        <div>
            <label for="per_page">На страницу</label>
            <select id="per_page" name="per_page">
                @foreach ([10, 25, 50, 100] as $size)
                    <option value="{{ $size }}" @selected($perPage == $size)>{{ $size }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <button type="submit" id="search-button">Найти</button>
        </div>
    </form>

    @if (!empty($provider_errors))
        <div class="alert">
            <strong>Часть источников недоступна:</strong>
            <ul>
                @foreach ($provider_errors as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($componentNum !== '')
        <div class="meta">Найдено предложений: {{ $offers->total() }}</div>

        @if ($offers->total() === 0)
            <div class="alert">По запросу «{{ $componentNum }}» предложений не найдено. Проверьте парт-номер или попробуйте другой.</div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Источник</th>
                        <th>Поставщик</th>
                        <th>Парт-номер</th>
                        <th>Бренд</th>
                        <th>Наличие</th>
                        <th>MOQ</th>
                        <th>Упаковка</th>
                        <th>Цена</th>
                        <th>Валюта</th>
                        <th>Срок (дней)</th>
                        <th>Ссылка</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($offers as $offer)
                        <tr>
                            <td>{{ $offer['provider'] ?? '-' }}</td>
                            <td>{{ $offer['supplier'] ?? '-' }}</td>
                            <td>{{ $offer['part_number'] ?? '-' }}</td>
                            <td>{{ $offer['brand'] ?? '-' }}</td>
                            <td>{{ $offer['stock'] ?? '-' }}</td>
                            <td>{{ $offer['min_order_qty'] ?? '-' }}</td>
                            <td>{{ $offer['packaging'] ?? '-' }}</td>
                            <td>{{ isset($offer['unit_price']) ? number_format((float)$offer['unit_price'], 4, '.', ' ') : '-' }}</td>
                            <td>{{ $offer['currency'] ?? '-' }}</td>
                            <td>{{ $offer['lead_time_days'] ?? '-' }}</td>
                            <td>
                                @if (!empty($offer['url']))
                                    <a href="{{ $offer['url'] }}" target="_blank" rel="noopener">Открыть</a>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="pagination">{{ $offers->links() }}</div>
        @endif
    @endif

    @if (!empty($timingsMs))
        <div class="timings">
            Время ответа: @foreach ($timingsMs as $provider => $timing)
                {{ $provider }}: {{ $timing }} ms
                @if (!$loop->last), @endif
            @endforeach
        </div>
    @endif

    <script>
        document.getElementById('search-form')?.addEventListener('submit', function () {
            var btn = document.getElementById('search-button');
            if (btn) { btn.disabled = true; btn.textContent = 'Поиск…'; }
        });
    </script>
</body>
</html>
