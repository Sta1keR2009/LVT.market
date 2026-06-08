<?php

namespace App\Services\Suppliers;

use App\Services\Suppliers\Contracts\SupplierSearchProviderInterface;
use App\Services\Suppliers\Data\ComponentSearchQuery;
use App\Services\Suppliers\Data\SupplierOffer;
use App\Services\Suppliers\Data\SupplierSearchResult;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Log;
use Throwable;

class SearchAggregator
{
    /**
     * @param array<int, SupplierSearchProviderInterface> $providers
     */
    public function __construct(
        private readonly array $providers,
    ) {
    }

    public function search(ComponentSearchQuery $query): SupplierSearchResult
    {
        $offers = [];
        $errors = [];
        $timingsMs = [];

        foreach ($this->providers as $provider) {
            $startedAt = microtime(true);
            try {
                $providerOffers = $provider->search($query);
                foreach ($providerOffers as $offer) {
                    if ($offer instanceof SupplierOffer) {
                        $offers[] = $offer;
                    }
                }
            } catch (Throwable $e) {
                $errors[] = $provider->name() . ': ' . $this->humanReadableError($e);
                Log::error('Supplier search provider failed.', [
                    'provider' => $provider->name(),
                    'component_number' => $query->componentNumber,
                    'message' => $e->getMessage(),
                ]);
            } finally {
                $timingsMs[$provider->name()] = round((microtime(true) - $startedAt) * 1000, 2);
            }
        }

        return new SupplierSearchResult($offers, $errors, $timingsMs);
    }

    private function humanReadableError(Throwable $e): string
    {
        if ($e instanceof ConnectionException) {
            return 'Таймаут или сетевая ошибка';
        }
        if ($e instanceof RequestException) {
            return $this->mapHttpStatusToError($e->response?->status());
        }
        $status = (int) $e->getCode();
        if ($status >= 400 && $status <= 599) {
            return $this->mapHttpStatusToError($status);
        }
        return $e->getMessage();
    }

    private function mapHttpStatusToError(?int $status): string
    {
        return match ($status) {
            401 => 'Неверный или истёкший токен API',
            403 => 'Доступ к API запрещён для данного токена',
            422 => 'Некорректный парт-номер (минимум 3 символа)',
            429 => 'Превышен лимит запросов',
            400 => 'Некорректные параметры запроса',
            default => $status !== null ? "Ошибка API (HTTP {$status})" : 'Ошибка запроса к API',
        };
    }
}
