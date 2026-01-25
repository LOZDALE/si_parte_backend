<?php
// map.php
// Genera una mappa SVG evidenziando il confine del paese e un marker per la città.
// Parametri GET: paese_id (opz), country (nome paese), lat, lon, width, height

header('Access-Control-Allow-Origin: *');
header('Content-Type: image/svg+xml; charset=UTF-8');

$country = isset($_GET['country']) ? $_GET['country'] : (isset($_GET['countryName']) ? $_GET['countryName'] : null);
$lat = isset($_GET['lat']) ? $_GET['lat'] : null;
$lon = isset($_GET['lon']) ? $_GET['lon'] : null;
$width = isset($_GET['width']) ? (int)$_GET['width'] : 800;
$height = isset($_GET['height']) ? (int)$_GET['height'] : 600;

if (!$country || !$lat || !$lon) {
    http_response_code(400);
    echo '<svg xmlns="http://www.w3.org/2000/svg" width="' . $width . '" height="' . $height . '">';
    echo '<rect width="100%" height="100%" fill="#eee"/>';
    echo '<text x="10" y="20" fill="#333">Missing parameters: country, lat, lon</text>';
    echo '</svg>';
    exit;
}

// Query Nominatim
$query = urlencode($country);
$url = "https://nominatim.openstreetmap.org/search.php?q={$query}&polygon_geojson=1&format=jsonv2";
$opts = [
    "http" => [
        "method" => "GET",
        "header" => "User-Agent: SiParteApp/1.0 (contact@example.com)\r\n"
    ]
];
$context = stream_context_create($opts);
$raw = @file_get_contents($url, false, $context);
if ($raw === false) {
    // Prova a usare bbox fornita come fallback
    $minLat = isset($_GET['minLat']) ? floatval($_GET['minLat']) : null;
    $maxLat = isset($_GET['maxLat']) ? floatval($_GET['maxLat']) : null;
    $minLon = isset($_GET['minLon']) ? floatval($_GET['minLon']) : null;
    $maxLon = isset($_GET['maxLon']) ? floatval($_GET['maxLon']) : null;

    if ($minLat !== null && $maxLat !== null && $minLon !== null && $maxLon !== null && $maxLat > $minLat && $maxLon > $minLon) {
        // Disegna bbox come poligono verde
        $projectBox = function($latp, $lonp) use ($minLat, $maxLat, $minLon, $maxLon, $width, $height) {
            $x = ($lonp - $minLon) / ($maxLon - $minLon) * $width;
            $y = ($maxLat - $latp) / ($maxLat - $minLat) * $height;
            return [(float)$x, (float)$y];
        };
        list($x1,$y1) = $projectBox($minLat, $minLon);
        list($x2,$y2) = $projectBox($minLat, $maxLon);
        list($x3,$y3) = $projectBox($maxLat, $maxLon);
        list($x4,$y4) = $projectBox($maxLat, $minLon);

        echo '<svg xmlns="http://www.w3.org/2000/svg" width="' . $width . '" height="' . $height . '">';
        echo '<rect width="100%" height="100%" fill="#eef6fb"/>';
        echo '<path d="M' . round($x1,2) . ' ' . round($y1,2) . ' L' . round($x2,2) . ' ' . round($y2,2) . ' L' . round($x3,2) . ' ' . round($y3,2) . ' L' . round($x4,2) . ' ' . round($y4,2) . ' Z" fill="#2ecc71" fill-opacity="0.65" stroke="#1b6f3a" stroke-width="1"/>';
        // marker città al centro del bbox
        $centerX = ($x1 + $x2 + $x3 + $x4) / 4;
        $centerY = ($y1 + $y2 + $y3 + $y4) / 4;
        echo '<circle cx="' . round($centerX,2) . '" cy="' . round($centerY,2) . '" r="8" fill="#0b62d6" stroke="#fff" stroke-width="2"/>';
        echo '</svg>';
        exit;
    }

    // fallback semplice: marker al centro
    echo '<svg xmlns="http://www.w3.org/2000/svg" width="' . $width . '" height="' . $height . '">';
    echo '<rect width="100%" height="100%" fill="#eef6fb"/>';
    echo '<circle cx="' . ($width/2) . '" cy="' . ($height/2) . '" r="10" fill="#0b62d6" stroke="#fff" stroke-width="2"/>';
    echo '</svg>';
    exit;
}

$resp = json_decode($raw, true);
if (!is_array($resp) || count($resp) === 0) {
    // vedi se bbox è fornita
    $minLat = isset($_GET['minLat']) ? floatval($_GET['minLat']) : null;
    $maxLat = isset($_GET['maxLat']) ? floatval($_GET['maxLat']) : null;
    $minLon = isset($_GET['minLon']) ? floatval($_GET['minLon']) : null;
    $maxLon = isset($_GET['maxLon']) ? floatval($_GET['maxLon']) : null;
    if ($minLat !== null && $maxLat !== null && $minLon !== null && $maxLon !== null && $maxLat > $minLat && $maxLon > $minLon) {
        $projectBox = function($latp, $lonp) use ($minLat, $maxLat, $minLon, $maxLon, $width, $height) {
            $x = ($lonp - $minLon) / ($maxLon - $minLon) * $width;
            $y = ($maxLat - $latp) / ($maxLat - $minLat) * $height;
            return [(float)$x, (float)$y];
        };
        list($x1,$y1) = $projectBox($minLat, $minLon);
        list($x2,$y2) = $projectBox($minLat, $maxLon);
        list($x3,$y3) = $projectBox($maxLat, $maxLon);
        list($x4,$y4) = $projectBox($maxLat, $minLon);
        echo '<svg xmlns="http://www.w3.org/2000/svg" width="' . $width . '" height="' . $height . '">';
        echo '<rect width="100%" height="100%" fill="#eef6fb"/>';
        echo '<path d="M' . round($x1,2) . ' ' . round($y1,2) . ' L' . round($x2,2) . ' ' . round($y2,2) . ' L' . round($x3,2) . ' ' . round($y3,2) . ' L' . round($x4,2) . ' ' . round($y4,2) . ' Z" fill="#2ecc71" fill-opacity="0.65" stroke="#1b6f3a" stroke-width="1"/>';
        $centerX = ($x1 + $x2 + $x3 + $x4) / 4;
        $centerY = ($y1 + $y2 + $y3 + $y4) / 4;
        echo '<circle cx="' . round($centerX,2) . '" cy="' . round($centerY,2) . '" r="8" fill="#0b62d6" stroke="#fff" stroke-width="2"/>';
        echo '</svg>';
        exit;
    }
    echo '<svg xmlns="http://www.w3.org/2000/svg" width="' . $width . '" height="' . $height . '">';
    echo '<rect width="100%" height="100%" fill="#eef6fb"/>';
    echo '<circle cx="' . ($width/2) . '" cy="' . ($height/2) . '" r="10" fill="#0b62d6" stroke="#fff" stroke-width="2"/>';
    echo '</svg>';
    exit;
}

$geo = null;
foreach ($resp as $r) {
    if (!empty($r['geojson'])) { $geo = $r['geojson']; break; }
}
if (!$geo) {
    // se geojson non presente, prova bbox fallback
    $minLat = isset($_GET['minLat']) ? floatval($_GET['minLat']) : null;
    $maxLat = isset($_GET['maxLat']) ? floatval($_GET['maxLat']) : null;
    $minLon = isset($_GET['minLon']) ? floatval($_GET['minLon']) : null;
    $maxLon = isset($_GET['maxLon']) ? floatval($_GET['maxLon']) : null;
    if ($minLat !== null && $maxLat !== null && $minLon !== null && $maxLon !== null && $maxLat > $minLat && $maxLon > $minLon) {
        $projectBox = function($latp, $lonp) use ($minLat, $maxLat, $minLon, $maxLon, $width, $height) {
            $x = ($lonp - $minLon) / ($maxLon - $minLon) * $width;
            $y = ($maxLat - $latp) / ($maxLat - $minLat) * $height;
            return [(float)$x, (float)$y];
        };
        list($x1,$y1) = $projectBox($minLat, $minLon);
        list($x2,$y2) = $projectBox($minLat, $maxLon);
        list($x3,$y3) = $projectBox($maxLat, $maxLon);
        list($x4,$y4) = $projectBox($maxLat, $minLon);
        echo '<svg xmlns="http://www.w3.org/2000/svg" width="' . $width . '" height="' . $height . '">';
        echo '<rect width="100%" height="100%" fill="#eef6fb"/>';
        echo '<path d="M' . round($x1,2) . ' ' . round($y1,2) . ' L' . round($x2,2) . ' ' . round($y2,2) . ' L' . round($x3,2) . ' ' . round($y3,2) . ' L' . round($x4,2) . ' ' . round($y4,2) . ' Z" fill="#2ecc71" fill-opacity="0.65" stroke="#1b6f3a" stroke-width="1"/>';
        $centerX = ($x1 + $x2 + $x3 + $x4) / 4;
        $centerY = ($y1 + $y2 + $y3 + $y4) / 4;
        echo '<circle cx="' . round($centerX,2) . '" cy="' . round($centerY,2) . '" r="8" fill="#0b62d6" stroke="#fff" stroke-width="2"/>';
        echo '</svg>';
        exit;
    }
    echo '<svg xmlns="http://www.w3.org/2000/svg" width="' . $width . '" height="' . $height . '">';
    echo '<rect width="100%" height="100%" fill="#eef6fb"/>';
    echo '<circle cx="' . ($width/2) . '" cy="' . ($height/2) . '" r="10" fill="#0b62d6" stroke="#fff" stroke-width="2"/>';
    echo '</svg>';
    exit;
}

$polygons = [];
if ($geo['type'] === 'Polygon') {
    $polygons[] = $geo['coordinates'];
} elseif ($geo['type'] === 'MultiPolygon') {
    $polygons = $geo['coordinates'];
} else {
    echo '<svg xmlns="http://www.w3.org/2000/svg" width="' . $width . '" height="' . $height . '">';
    echo '<rect width="100%" height="100%" fill="#eef6fb"/>';
    echo '<circle cx="' . ($width/2) . '" cy="' . ($height/2) . '" r="10" fill="#0b62d6" stroke="#fff" stroke-width="2"/>';
    echo '</svg>';
    exit;
}

// bbox
$minLat = 90; $maxLat = -90; $minLon = 180; $maxLon = -180;
foreach ($polygons as $poly) {
    foreach ($poly as $ring) {
        foreach ($ring as $pt) {
            $lonp = $pt[0]; $latp = $pt[1];
            if ($latp < $minLat) $minLat = $latp;
            if ($latp > $maxLat) $maxLat = $latp;
            if ($lonp < $minLon) $minLon = $lonp;
            if ($lonp > $maxLon) $maxLon = $lonp;
        }
    }
}
$latPad = ($maxLat - $minLat) * 0.05; $lonPad = ($maxLon - $minLon) * 0.05;
$minLat -= $latPad; $maxLat += $latPad; $minLon -= $lonPad; $maxLon += $lonPad;

$project = function($latp, $lonp) use ($minLat, $maxLat, $minLon, $maxLon, $width, $height) {
    $x = ($lonp - $minLon) / ($maxLon - $minLon) * $width;
    $y = ($maxLat - $latp) / ($maxLat - $minLat) * $height;
    return [(float)$x, (float)$y];
};

$paths = [];
foreach ($polygons as $poly) {
    foreach ($poly as $ring) {
        $d = '';
        $first = true;
        foreach ($ring as $pt) {
            $lonp = $pt[0]; $latp = $pt[1];
            list($x,$y) = $project($latp, $lonp);
            $d .= ($first ? 'M' : 'L') . round($x,2) . ' ' . round($y,2) . ' ';
            $first = false;
        }
        $d .= 'Z';
        $paths[] = $d;
    }
}

list($cityX, $cityY) = $project(floatval($lat), floatval($lon));

$svg = '<?xml version="1.0" encoding="UTF-8"?>\n';
$svg .= "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"{$width}\" height=\"{$height}\" viewBox=\"0 0 {$width} {$height}\">\n";
$svg .= "<rect width=\"100%\" height=\"100%\" fill=\"#eef6fb\"/>\n";
foreach ($paths as $p) {
    $svg .= "<path d=\"{$p}\" fill=\"#2ecc71\" fill-opacity=\"0.65\" stroke=\"#1b6f3a\" stroke-width=\"1\"/>\n";
}
$svg .= "<circle cx=\"" . round($cityX,2) . "\" cy=\"" . round($cityY,2) . "\" r=\"8\" fill=\"#0b62d6\" stroke=\"#ffffff\" stroke-width=\"2\"/>\n";
$svg .= "</svg>\n";

echo $svg;
exit;
