<?php
/**
 * Verification Script for Calendar Fix
 * Uso: incluir este archivo en una página de admin o ejecutar vía WP-CLI
 */

// Solo ejecutar en contexto de WordPress
if (!defined('ABSPATH')) {
    echo "Este script debe ejecutarse dentro de WordPress.\n";
    exit;
}

echo "<h2>Verificación de la Corrección del Calendario</h2>";

// 1. Verificar si existen vendors
$vendors = get_posts([
    'post_type' => 'hp_vendor',
    'post_status' => 'publish',
    'numberposts' => 1
]);

if (empty($vendors)) {
    echo "<p style='color:red;'>❌ No se encontraron vendors publicados.</p>";
    exit;
}

$vendor_id = $vendors[0]->ID;
echo "<p style='color:green;'>✅ Vendor encontrado: ID {$vendor_id} - {$vendors[0]->post_title}</p>";

// 2. Verificar listings del vendor
$listings = VDP_Bookings::get_vendor_listings($vendor_id);
echo "<p>📋 Listings encontrados: " . count($listings) . "</p>";

if (empty($listings)) {
    echo "<p style='color:orange;'>⚠️ No hay listings para este vendor. Necesitas crear listings para ver bookings.</p>";
} else {
    foreach ($listings as $listing_id) {
        $listing = get_post($listing_id);
        echo "<p>• Listing: {$listing->post_title} (ID: {$listing_id})</p>";
    }
}

// 3. Verificar bookings
$bookings_query = [
    'post_type' => 'hp_booking',
    'post_status' => ['publish', 'pending', 'draft', 'private'],
    'post_parent__in' => !empty($listings) ? $listings : [0],
    'posts_per_page' => 5
];

$bookings = get_posts($bookings_query);
echo "<p>📅 Bookings encontrados: " . count($bookings) . "</p>";

if (!empty($bookings)) {
    foreach ($bookings as $booking) {
        $start_time = get_post_meta($booking->ID, 'hp_start_time', true);
        $start_date = get_post_meta($booking->ID, 'hp_start_date', true);
        
        echo "<p>• Booking ID: {$booking->ID}, Status: {$booking->post_status}";
        echo ", Start Time: " . ($start_time ? date('Y-m-d H:i:s', $start_time) : 'N/A');
        echo ", Start Date: " . ($start_date ?: 'N/A') . "</p>";
    }
}

// 4. Probar el método get_vendor_calendar_data
echo "<h3>Probando get_vendor_calendar_data()</h3>";
$calendar_data = VDP_Bookings::get_vendor_calendar_data($vendor_id);
echo "<p>📊 Eventos de calendario generados: " . count($calendar_data) . "</p>";

if (!empty($calendar_data)) {
    echo "<h4>Primeros 3 eventos:</h4>";
    foreach (array_slice($calendar_data, 0, 3) as $event) {
        echo "<p>• {$event['title']} - {$event['start']} to {$event['end']} - Status: {$event['status']}</p>";
    }
}

// 5. Probar el método del calendario principal
echo "<h3>Probando VDP_Calendar::get_calendar_data()</h3>";
$main_calendar_data = VDP_Calendar::get_calendar_data($vendor_id);
echo "<p>🗓️ Estructura del calendario principal:</p>";
echo "<p>• Events: " . (isset($main_calendar_data['events']) ? count($main_calendar_data['events']) : 0) . "</p>";
echo "<p>• Price Ranges: " . (isset($main_calendar_data['price_ranges']) ? count($main_calendar_data['price_ranges']) : 0) . "</p>";
echo "<p>• Listings: " . (isset($main_calendar_data['listings']) ? count($main_calendar_data['listings']) : 0) . "</p>";

echo "<hr>";
echo "<h3>🎯 Resumen de Correcciones Aplicadas:</h3>";
echo "<ol>";
echo "<li>✅ Corregida inconsistencia en <code>calendarData.events</code> vs <code>calendarData</code> en bookings-content.php</li>";
echo "<li>✅ Expandidos los estados de booking incluidos en el calendario (ahora incluye: publish, pending, draft, private)</li>";
echo "<li>✅ Mejorada la lógica de fechas con múltiples fallbacks</li>";
echo "<li>✅ Añadidos indicadores de estado en los títulos de eventos</li>";
echo "<li>✅ Mejorada la estructura de datos de eventos con extendedProps</li>";
echo "<li>✅ Añadida depuración de consola JavaScript</li>";
echo "<li>✅ Aplicadas correcciones similares al calendario principal</li>";
echo "</ol>";

echo "<h3>📝 Próximos pasos:</h3>";
echo "<ol>";
echo "<li>Visita la página de Bookings del dashboard del vendor</li>";
echo "<li>Cambia a la vista de calendario</li>";
echo "<li>Abre la consola del navegador (F12) para ver logs de depuración</li>";
echo "<li>Si no aparecen eventos, revisa que los bookings tengan fechas válidas en los meta campos</li>";
echo "</ol>";

if (empty($bookings)) {
    echo "<p style='color:orange;'>⚠️ <strong>Nota:</strong> No hay bookings para mostrar. Crea algunos bookings de prueba para verificar que el calendario funciona correctamente.</p>";
}

echo "<p><em>Script ejecutado el " . date('Y-m-d H:i:s') . "</em></p>";
?>