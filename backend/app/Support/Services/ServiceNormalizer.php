<?php

namespace App\Support\Services;

class ServiceNormalizer
{
    /**
     * Normalize a service object for the frontend.
     * Ensure id, type (origin), and react_key are always present.
     *
     * @param mixed $service
     * @param string $origin 'global' | 'local'
     * @return array
     */
    public static function normalize($service, string $origin): array
    {
        $serviceArray = is_array($service) ? $service : (method_exists($service, 'toArray') ? $service->toArray() : (array) $service);

        return array_merge($serviceArray, [
            'id'           => (int) $serviceArray['id'],
            'type'         => $origin, // local | global
            'react_key'    => $origin . '_' . $serviceArray['id'],

            'name'         => $serviceArray['item_name'] ?? $serviceArray['name'] ?? 'Unknown Service',
            'item_name'    => $serviceArray['item_name'] ?? $serviceArray['name'] ?? 'Unknown Service',

            'default_fee'  => $serviceArray['default_fee'] ?? $serviceArray['custom_price'] ?? 0,
            'category_id'  => $serviceArray['category_id'] ?? null,
            'specialty_id' => $serviceArray['specialty_id'] ?? null,
        ]);
    }
}
