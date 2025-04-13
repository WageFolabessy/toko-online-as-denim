<?php

namespace App\Http\Controllers\SiteUser;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductsPaginatedResource;
use App\Models\Product;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Elastic\Transport\Exception\NoNodeAvailableException;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProductSearchController extends Controller
{
    private Client $elasticsearch;

    public function __construct(Client $elasticsearch)
    {
        $this->elasticsearch = $elasticsearch;
    }

    public function search(Request $request): ProductsPaginatedResource|JsonResponse
    {
        Log::info('Product search request received:', $request->query());
        $searchParams = [];

        try {
            $searchParams = $this->buildElasticsearchParams($request);
            Log::debug('Executing Elasticsearch Query:', $searchParams);

            $response = $this->elasticsearch->search($searchParams)->asArray();
            Log::info('Elasticsearch Response Hits Count:', ['count' => count($response['hits']['hits'] ?? [])]);

            $productIds = collect($response['hits']['hits'] ?? [])->pluck('_id')->toArray();
            $totalHits = is_array($response['hits']['total']) ? $response['hits']['total']['value'] : ($response['hits']['total'] ?? 0);

            Log::info('Product IDs from ES:', ['count' => count($productIds)]);
            Log::info('Total Hits from ES:', ['total' => $totalHits]);

            $products = $this->fetchProductsFromDatabase($productIds);
            Log::info('Products fetched from DB:', ['count' => $products->count()]);

            $paginator = $this->createPaginator($products, $totalHits, $request);

            return new ProductsPaginatedResource($paginator);
        } catch (ClientResponseException $e) {
            $statusCode = $e->getCode();
            $errorMessage = $e->getMessage();
            $logContext = ['exception_class' => get_class($e), 'trace_snippet' => $e->getTraceAsString()];

            if ($statusCode === 404) {
                Log::warning('Search failed: Index "products" not found.', ['exception' => $errorMessage]);
                return $this->handleSearchError($e, 'Sumber data pencarian tidak ditemukan.', 404, true, $request);
            } elseif ($statusCode === 400) {
                Log::error('Elasticsearch Bad Request: ' . $errorMessage, array_merge($logContext, ['query_sent' => $searchParams]));
                return $this->handleSearchError($e, 'Terjadi kesalahan pada parameter pencarian.', 400, false, $request, ['query' => $searchParams]);
            } else {
                Log::error('Elasticsearch Client Error (' . $statusCode . '): ' . $errorMessage, $logContext);
                return $this->handleSearchError($e, 'Terjadi masalah saat berkomunikasi dengan server pencarian.', $statusCode ?: 400, false, $request);
            }
        } catch (ServerResponseException $e) {
            $statusCode = $e->getCode();
            Log::error('Elasticsearch Server Error (' . $statusCode . '): ' . $e->getMessage(), ['exception_class' => get_class($e), 'trace_snippet' => $e->getTraceAsString()]);
            return $this->handleSearchError($e, 'Server pencarian sedang mengalami gangguan.', $statusCode ?: 503, false, $request);
        } catch (NoNodeAvailableException $e) {
            Log::critical('Elasticsearch connection failed: No nodes available.', ['exception' => $e->getMessage()]);
            return $this->handleSearchError($e, 'Tidak dapat terhubung ke server pencarian.', 503);
        } catch (Throwable $e) {
            return $this->handleSearchError($e, 'Terjadi kesalahan internal saat melakukan pencarian.', 500);
        }
    }

    private function buildElasticsearchParams(Request $request): array
    {
        $keyword = $request->query('keyword', '');
        $categoryId = $request->query('category_id');
        $brand = $request->query('brand');
        $color = $request->query('color');
        $size = $request->query('size');
        $sortBy = $request->query('sort_by', '_score');
        $sortOrder = $request->query('sort_order', 'desc');
        $perPage = filter_var($request->query('per_page', 12), FILTER_VALIDATE_INT, ['options' => ['default' => 12, 'min_range' => 1]]);
        $page = filter_var($request->query('page', 1), FILTER_VALIDATE_INT, ['options' => ['default' => 1, 'min_range' => 1]]);
        $categoryId = $categoryId ? filter_var($categoryId, FILTER_VALIDATE_INT) : null;

        $esQueryBody = [
            'query' => ['bool' => ['must' => [], 'filter' => []]],
            'sort' => [],
            'track_total_hits' => true,
        ];

        if (!empty($keyword)) {
            $esQueryBody['query']['bool']['must'][] = [
                'multi_match' => [
                    'query' => $keyword,
                    'fields' => [
                        'product_name^5',
                        'brand^4',
                        'category_name^3',
                        'color^3',
                        'size^2',
                        'description^1'
                    ],
                    'fuzziness' => 'AUTO',
                    'operator' => 'or'
                ]
            ];
            if ($sortBy === 'updated_at' && $sortOrder === 'desc') {
                $sortBy = '_score';
            }
        } else {
            $esQueryBody['query']['bool']['must'][] = ['match_all' => new \stdClass()];
            if ($sortBy === '_score') {
                $sortBy = 'updated_at';
                $sortOrder = 'desc';
            }
        }

        if ($categoryId) {
            $esQueryBody['query']['bool']['filter'][] = ['term' => ['category_id' => $categoryId]];
        }
        if ($brand) {
            $esQueryBody['query']['bool']['filter'][] = ['term' => ['brand.keyword' => $brand]];
        }
        if ($color) {
            $esQueryBody['query']['bool']['filter'][] = ['term' => ['color.keyword' => $color]];
        }
        if ($size) {
            $esQueryBody['query']['bool']['filter'][] = ['term' => ['size.keyword' => $size]];
        }

        $allowedSortFields = ['updated_at', 'created_at', 'original_price', 'sale_price'];
        if ($sortBy === '_score' && !empty($keyword)) {
            unset($esQueryBody['sort']);
        } elseif (in_array($sortBy, $allowedSortFields)) {
            $esQueryBody['sort'] = [$sortBy => ['order' => strtolower($sortOrder) === 'asc' ? 'asc' : 'desc']];
        } else {
            $esQueryBody['sort'] = ['updated_at' => ['order' => 'desc']];
        }

        return [
            'index' => 'products',
            'body' => $esQueryBody,
            'from' => ($page - 1) * $perPage,
            'size' => $perPage,
        ];
    }

    private function fetchProductsFromDatabase(array $productIds): EloquentCollection
    {
        if (empty($productIds)) {
            return new EloquentCollection();
        }
        return Product::with(['images', 'category'])
            ->whereIn('id', $productIds)
            ->orderByRaw('FIELD(id, ' . SupportCollection::make($productIds)->implode(',') . ')')
            ->get();
    }

    private function createPaginator(EloquentCollection $products, int $totalHits, Request $request): LengthAwarePaginator
    {
        $perPage = filter_var($request->query('per_page', 12), FILTER_VALIDATE_INT, ['options' => ['default' => 12]]);
        $page = filter_var($request->query('page', 1), FILTER_VALIDATE_INT, ['options' => ['default' => 1]]);
        return new LengthAwarePaginator(
            $products,
            $totalHits,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }

    private function handleSearchError(Throwable $e, string $logMessage, int $statusCode, bool $returnEmptyResource = false, ?Request $request = null, array $context = []): JsonResponse|ProductsPaginatedResource
    {
        Log::error($logMessage . ': ' . $e->getMessage(), array_merge(['exception_class' => get_class($e), 'trace_snippet' => $e->getTraceAsString()], $context));

        if ($returnEmptyResource && $request) {
            $perPage = filter_var($request->query('per_page', 12), FILTER_VALIDATE_INT, ['options' => ['default' => 12]]);
            $page = filter_var($request->query('page', 1), FILTER_VALIDATE_INT, ['options' => ['default' => 1]]);
            return new ProductsPaginatedResource(new LengthAwarePaginator([], 0, $perPage, $page, [
                'path' => $request->url(),
                'query' => $request->query()
            ]));
        }
        $userMessage = ($statusCode === 500 || $statusCode === 503) ? 'Terjadi gangguan pada server pencarian. Coba lagi nanti.' : $logMessage;
        return response()->json(['message' => $userMessage], $statusCode);
    }
}
