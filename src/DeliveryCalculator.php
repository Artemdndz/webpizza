<?php
require_once 'config.php';

class DeliveryCalculator {
    private $restaurantLat;
    private $restaurantLng;
    
    public function __construct() {
        $this->restaurantLat = RESTAURANT_LAT;
        $this->restaurantLng = RESTAURANT_LNG;
    }
    
    /**
     * Рассчитать расстояние между двумя точками (в км)
     */
    public function calculateDistance($lat1, $lng1, $lat2, $lng2) {
        $earthRadius = 6371; // Радиус Земли в км
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        
        $a = sin($dLat/2) * sin($dLat/2) + 
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * 
             sin($dLng/2) * sin($dLng/2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        $distance = $earthRadius * $c;
        
        return round($distance, 2);
    }
    
    /**
     * Определить зону доставки по расстоянию
     */
    public function getDeliveryZone($distance) {
        if ($distance <= DELIVERY_ZONE_1_MAX_KM) {
            return 1;
        } elseif ($distance <= DELIVERY_ZONE_2_MAX_KM) {
            return 2;
        } elseif ($distance <= DELIVERY_ZONE_3_MAX_KM) {
            return 3;
        } elseif ($distance <= DELIVERY_ZONE_4_MAX_KM) {
            return 4;
        } else {
            return 0; // Доставка невозможна
        }
    }
    
    /**
     * Рассчитать стоимость доставки
     */
    public function calculateDeliveryCost($zone, $orderTotal) {
        switch ($zone) {
            case 1:
                if ($orderTotal >= DELIVERY_ZONE_1_FREE) return 0;
                if ($orderTotal >= DELIVERY_ZONE_1_MIN_ORDER) return DELIVERY_ZONE_1_COST;
                return false; // Мин сумма не набрана
                
            case 2:
                if ($orderTotal >= DELIVERY_ZONE_2_FREE) return 0;
                if ($orderTotal >= DELIVERY_ZONE_2_MIN_ORDER) return DELIVERY_ZONE_2_COST;
                return false;
                
            case 3:
                if ($orderTotal >= DELIVERY_ZONE_3_FREE) return 0;
                if ($orderTotal >= DELIVERY_ZONE_3_MIN_ORDER) return DELIVERY_ZONE_3_COST;
                return false;
                
            case 4:
                // Для зоны 4 нужен дополнительный API для расчета такси
                // Пока возвращаем "taxi" как флаг
                if ($orderTotal >= DELIVERY_ZONE_3_FREE) {
                    return ['type' => 'free', 'discount' => DELIVERY_ZONE_4_DISCOUNT];
                }
                return ['type' => 'taxi', 'discount' => DELIVERY_ZONE_4_DISCOUNT];
                
            default:
                return false;
        }
    }
    
    /**
     * Получить сообщение о минимальной сумме
     */
    public function getMinOrderMessage($zone, $currentTotal) {
        switch ($zone) {
            case 1:
                $needed = DELIVERY_ZONE_1_MIN_ORDER - $currentTotal;
                $forFree = DELIVERY_ZONE_1_FREE - $currentTotal;
                return [
                    'min_order' => DELIVERY_ZONE_1_MIN_ORDER,
                    'needed' => max(0, $needed),
                    'for_free' => max(0, $forFree)
                ];
                
            case 2:
                $needed = DELIVERY_ZONE_2_MIN_ORDER - $currentTotal;
                $forFree = DELIVERY_ZONE_2_FREE - $currentTotal;
                return [
                    'min_order' => DELIVERY_ZONE_2_MIN_ORDER,
                    'needed' => max(0, $needed),
                    'for_free' => max(0, $forFree)
                ];
                
            case 3:
                $needed = DELIVERY_ZONE_3_MIN_ORDER - $currentTotal;
                $forFree = DELIVERY_ZONE_3_FREE - $currentTotal;
                return [
                    'min_order' => DELIVERY_ZONE_3_MIN_ORDER,
                    'needed' => max(0, $needed),
                    'for_free' => max(0, $forFree)
                ];
                
            case 4:
                return [
                    'min_order' => 0,
                    'needed' => 0,
                    'for_free' => max(0, DELIVERY_ZONE_3_FREE - $currentTotal)
                ];
                
            default:
                return null;
        }
    }
    
    /**
     * Геокодирование адреса через Google Maps
     */
    public function geocodeAddress($address) {
        $url = "https://maps.googleapis.com/maps/api/geocode/json";
        $params = [
            'address' => $address . ', Дніпро, Україна',
            'key' => GOOGLE_MAPS_API_KEY
        ];
        
        $response = file_get_contents($url . '?' . http_build_query($params));
        $data = json_decode($response, true);
        
        if ($data['status'] === 'OK') {
            $location = $data['results'][0]['geometry']['location'];
            return [
                'lat' => $location['lat'],
                'lng' => $location['lng'],
                'formatted_address' => $data['results'][0]['formatted_address']
            ];
        }
        
        return null;
    }
    
    /**
     * Получить координаты по IP (для автоматического определения)
     */
    public function getCoordinatesByIP() {
        // Можно использовать бесплатные сервисы или определить по браузеру
        // Для MVP пока возвращаем null
        return null;
    }
}