<?php
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/functions.php';

class CourierController {
    public static function getAvailableCouriers() {
        // In a real application, this would fetch from a database or an external API
        return [
            ['id' => 1, 'name' => 'Packzy Express', 'logo' => 'packzy.png'],
            ['id' => 2, 'name' => 'FastShip Logistics', 'logo' => 'fastship.png'],
        ];
    }

    public static function getTrackingInfo($trackingId) {
        // In a real application, this would call an external courier API
        if (!empty($trackingId)) {
            return [
                'trackingId' => $trackingId,
                'status' => 'In Transit',
                'lastUpdate' => date('Y-m-d H:i:s'),
                'details' => [
                    'Package picked up',
                    'Departed sorting facility',
                    'Arrived at local hub',
                ]
            ];
        }
        return null;
    }
}
