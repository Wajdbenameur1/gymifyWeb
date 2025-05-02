<?php

namespace App\Service;

use Google_Client;
use Google_Service_ShoppingContent;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Psr\Log\LoggerInterface;

class GoogleShoppingService
{
    private $client;
    private $shoppingService;
    private $merchantId;
    private $logger;

    public function __construct(string $merchantId, string $credentialsPath, LoggerInterface $logger)
    {
        $this->merchantId = $merchantId;
        $this->logger = $logger;
        
        try {
            // Initialize Google Client
            $this->client = new Google_Client();
            $this->client->setApplicationName('Ma Boutique Sport');
            $this->client->setScopes(Google_Service_ShoppingContent::CONTENT);
            $this->client->setAuthConfig($credentialsPath);
            
            // Create Shopping Content Service
            $this->shoppingService = new Google_Service_ShoppingContent($this->client);
        } catch (\Exception $e) {
            $this->logger->error('Google Shopping API initialization error: ' . $e->getMessage());
        }
    }

    /**
     * Search for product information
     */
    public function searchProduct(string $query): array
    {
        try {
            $products = $this->shoppingService->products->listProducts($this->merchantId, [
                'maxResults' => 10,
                'query' => $query
            ]);

            $results = [];
            foreach ($products->getResources() as $product) {
                $results[] = [
                    'title' => $product->getTitle(),
                    'description' => $product->getDescription(),
                    'price' => [
                        'value' => $product->getPrice()->getValue(),
                        'currency' => $product->getPrice()->getCurrency(),
                    ],
                    'link' => $product->getLink(),
                    'image' => $product->getImageLink(),
                    'brand' => $product->getBrand(),
                    'availability' => $product->getAvailability(),
                ];
            }

            return $results;
        } catch (\Exception $e) {
            // Log the error and return empty array
            return [];
        }
    }

    /**
     * Get product details by ID
     */
    public function getProductDetails(string $productId): ?array
    {
        try {
            $product = $this->shoppingService->products->get($this->merchantId, $productId);

            return [
                'title' => $product->getTitle(),
                'description' => $product->getDescription(),
                'price' => [
                    'value' => $product->getPrice()->getValue(),
                    'currency' => $product->getPrice()->getCurrency(),
                ],
                'link' => $product->getLink(),
                'image' => $product->getImageLink(),
                'brand' => $product->getBrand(),
                'availability' => $product->getAvailability(),
                'gtin' => $product->getGtin(),
                'mpn' => $product->getMpn(),
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Search for similar products with exactly the same price (within 5% range)
     */
    public function searchSimilarProducts(string $productName, float $targetPrice): array
    {
        try {
            // Search for products using the Content API
            $products = $this->shoppingService->products->listProducts($this->merchantId, [
                'maxResults' => 4,
                'query' => $productName
            ]);

            $results = [];
            foreach ($products->getResources() as $product) {
                // Filter products with exactly the same price (within 5% range)
                $price = $product->getPrice()->getValue();
                if (abs($price - $targetPrice) <= ($targetPrice * 0.05)) {
                    $results[] = [
                        'title' => $product->getTitle(),
                        'description' => $product->getDescription(),
                        'price' => [
                            'value' => $price,
                            'currency' => $product->getPrice()->getCurrency(),
                        ],
                        'link' => $product->getLink(),
                        'image' => $product->getImageLink(),
                        'brand' => $product->getBrand(),
                        'availability' => $product->getAvailability(),
                        'gtin' => $product->getGtin(),
                        'store' => $product->getChannel(),
                    ];
                }
            }

            return $results;
        } catch (\Exception $e) {
            $this->logger->error('Google Shopping API search error: ' . $e->getMessage());
            return [];
        }
    }
} 