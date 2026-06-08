<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/digikey/DigiKeyAPI.php';

echo '<h1>🔍 Digi-Key API Test - Search 10 Random Products</h1>';

try {
    $digikey = new DigiKeyAPI();
    
    echo '<h2>Searching for 10 random electronic components...</h2>';
    
    // 🔍 ВОТ ГДЕ ПРОИСХОДИТ ПОИСК 10 РАНДОМНЫХ ТОВАРОВ
    $products = $digikey->searchRandomProducts(10);
    
    if (isset($products['Products']) && count($products['Products']) > 0) {
        echo '<div style="color: green; font-size: 18px; margin-bottom: 20px;">';
        echo '✅ Successfully found ' . count($products['Products']) . ' products!';
        echo '</div>';
        
        foreach ($products['Products'] as $index => $product) {
            echo '<div style="border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px;">';
            echo '<h3>' . ($index + 1) . '. ' . ($product['ManufacturerPartNumber'] ?? 'N/A') . '</h3>';
            echo '<p><strong>Manufacturer:</strong> ' . ($product['Manufacturer']['Value'] ?? 'N/A') . '</p>';
            echo '<p><strong>Description:</strong> ' . ($product['ProductDescription'] ?? 'N/A') . '</p>';
            echo '<p><strong>DigiKey Part Number:</strong> ' . ($product['DigiKeyPartNumber'] ?? 'N/A') . '</p>';
            echo '<p><strong>Quantity Available:</strong> ' . ($product['QuantityAvailable'] ?? 'N/A') . '</p>';
            
            if (isset($product['StandardPricing']) && is_array($product['StandardPricing'])) {
                echo '<p><strong>Pricing:</strong>';
                foreach ($product['StandardPricing'] as $price) {
                    echo ' ' . $price['BreakQuantity'] . '+ : $' . $price['UnitPrice'] . ' |';
                }
                echo '</p>';
            }
            
            echo '</div>';
        }
    } else {
        echo '<div style="color: orange;">No products found or API returned empty result.</div>';
    }
    
} catch (Exception $e) {
    echo '<div style="background: #f8d7da; color: #721c24; padding: 20px; border-radius: 5px;">';
    echo '<h3>❌ API Test Failed</h3>';
    echo '<p><strong>Error:</strong> ' . $e->getMessage() . '</p>';
    
    if (strpos($e->getMessage(), 'No access token') !== false) {
        echo '<p><a href="/local/php_interface/include/digikey/auth.php" style="background: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 3px;">';
        echo '🔑 First, authorize with Digi-Key';
        echo '</a></p>';
    }
    echo '</div>';
}