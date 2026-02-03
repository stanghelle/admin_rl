<?php
/**
 * Traffic Analytics API
 * Handles recording visits and retrieving traffic statistics
 */
require_once __DIR__ . '/../core/init.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// For recording visits, allow without auth (public tracking)
// For retrieving stats, require authentication
if ($action !== 'record') {
    Auth::requireApiAuth();
}

$db = DB::getInstance();

switch ($action) {
    case 'record':
        recordVisit($db);
        break;
    case 'stats':
        getTrafficStats($db);
        break;
    case 'hourly':
        getHourlyStats($db);
        break;
    case 'daily':
        getDailyStats($db);
        break;
    case 'weekly':
        getWeeklyStats($db);
        break;
    case 'monthly':
        getMonthlyStats($db);
        break;
    case 'pages':
        getTopPages($db);
        break;
    case 'devices':
        getDeviceStats($db);
        break;
    case 'browsers':
        getBrowserStats($db);
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
        exit;
}

/**
 * Record a page visit
 */
function recordVisit($db) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        return;
    }

    $pageUrl = $_POST['page_url'] ?? '/';
    $pageTitle = $_POST['page_title'] ?? '';
    $referrer = $_POST['referrer'] ?? $_SERVER['HTTP_REFERER'] ?? '';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
    $sessionId = session_id() ?: uniqid('sess_', true);

    // Detect device type
    $deviceType = 'desktop';
    if (preg_match('/Mobile|Android|iPhone|iPad/i', $userAgent)) {
        if (preg_match('/iPad|Tablet/i', $userAgent)) {
            $deviceType = 'tablet';
        } else {
            $deviceType = 'mobile';
        }
    }

    // Detect browser
    $browser = 'Other';
    if (preg_match('/Chrome/i', $userAgent) && !preg_match('/Edge/i', $userAgent)) {
        $browser = 'Chrome';
    } elseif (preg_match('/Firefox/i', $userAgent)) {
        $browser = 'Firefox';
    } elseif (preg_match('/Safari/i', $userAgent) && !preg_match('/Chrome/i', $userAgent)) {
        $browser = 'Safari';
    } elseif (preg_match('/Edge/i', $userAgent)) {
        $browser = 'Edge';
    } elseif (preg_match('/Opera|OPR/i', $userAgent)) {
        $browser = 'Opera';
    } elseif (preg_match('/MSIE|Trident/i', $userAgent)) {
        $browser = 'IE';
    }

    // Detect OS
    $os = 'Other';
    if (preg_match('/Windows/i', $userAgent)) {
        $os = 'Windows';
    } elseif (preg_match('/Mac/i', $userAgent)) {
        $os = 'macOS';
    } elseif (preg_match('/Linux/i', $userAgent)) {
        $os = 'Linux';
    } elseif (preg_match('/Android/i', $userAgent)) {
        $os = 'Android';
    } elseif (preg_match('/iOS|iPhone|iPad/i', $userAgent)) {
        $os = 'iOS';
    }

    try {
        $result = $db->insert('website_traffic', [
            'page_url' => substr($pageUrl, 0, 500),
            'page_title' => substr($pageTitle, 0, 255),
            'referrer' => substr($referrer, 0, 500),
            'user_agent' => substr($userAgent, 0, 500),
            'ip_address' => $ipAddress,
            'session_id' => $sessionId,
            'device_type' => $deviceType,
            'browser' => $browser,
            'os' => $os
        ]);

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to record visit']);
    }
}

/**
 * Get overall traffic statistics
 */
function getTrafficStats($db) {
    $period = $_GET['period'] ?? 'week';

    $intervals = [
        'today' => 'DATE(visited_at) = CURDATE()',
        'yesterday' => 'DATE(visited_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)',
        'week' => 'visited_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)',
        'month' => 'visited_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)',
        'year' => 'visited_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)'
    ];

    $where = $intervals[$period] ?? $intervals['week'];

    // Total visits
    $totalVisits = $db->query("SELECT COUNT(*) as total FROM website_traffic WHERE {$where}");
    $total = $totalVisits->count() ? $totalVisits->first()->total : 0;

    // Unique visitors (by session)
    $uniqueVisitors = $db->query("SELECT COUNT(DISTINCT session_id) as total FROM website_traffic WHERE {$where}");
    $unique = $uniqueVisitors->count() ? $uniqueVisitors->first()->total : 0;

    // Previous period for comparison
    $prevIntervals = [
        'today' => 'DATE(visited_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)',
        'yesterday' => 'DATE(visited_at) = DATE_SUB(CURDATE(), INTERVAL 2 DAY)',
        'week' => 'visited_at >= DATE_SUB(NOW(), INTERVAL 14 DAY) AND visited_at < DATE_SUB(NOW(), INTERVAL 7 DAY)',
        'month' => 'visited_at >= DATE_SUB(NOW(), INTERVAL 60 DAY) AND visited_at < DATE_SUB(NOW(), INTERVAL 30 DAY)',
        'year' => 'visited_at >= DATE_SUB(NOW(), INTERVAL 2 YEAR) AND visited_at < DATE_SUB(NOW(), INTERVAL 1 YEAR)'
    ];

    $prevWhere = $prevIntervals[$period] ?? $prevIntervals['week'];
    $prevVisits = $db->query("SELECT COUNT(*) as total FROM website_traffic WHERE {$prevWhere}");
    $prevTotal = $prevVisits->count() ? $prevVisits->first()->total : 0;

    // Calculate change percentage
    $change = $prevTotal > 0 ? round((($total - $prevTotal) / $prevTotal) * 100, 1) : 0;

    echo json_encode([
        'success' => true,
        'data' => [
            'total_visits' => (int)$total,
            'unique_visitors' => (int)$unique,
            'previous_visits' => (int)$prevTotal,
            'change_percent' => $change,
            'period' => $period
        ]
    ]);
}

/**
 * Get hourly statistics for the last 24 hours
 */
function getHourlyStats($db) {
    $data = $db->query("
        SELECT
            HOUR(visited_at) as hour,
            COUNT(*) as visits,
            COUNT(DISTINCT session_id) as unique_visitors
        FROM website_traffic
        WHERE visited_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        GROUP BY HOUR(visited_at)
        ORDER BY hour ASC
    ");

    $hours = array_fill(0, 24, ['visits' => 0, 'unique' => 0]);

    if ($data->count() > 0) {
        foreach ($data->results() as $row) {
            $hours[(int)$row->hour] = [
                'visits' => (int)$row->visits,
                'unique' => (int)$row->unique_visitors
            ];
        }
    }

    $labels = [];
    $visits = [];
    $unique = [];

    for ($i = 0; $i < 24; $i++) {
        $labels[] = sprintf('%02d:00', $i);
        $visits[] = $hours[$i]['visits'];
        $unique[] = $hours[$i]['unique'];
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'labels' => $labels,
            'visits' => $visits,
            'unique_visitors' => $unique
        ]
    ]);
}

/**
 * Get daily statistics for the last 7 days
 */
function getDailyStats($db) {
    $days = (int)($_GET['days'] ?? 7);
    $days = min(max($days, 1), 90); // Limit to 1-90 days

    $data = $db->query("
        SELECT
            DATE(visited_at) as date,
            COUNT(*) as visits,
            COUNT(DISTINCT session_id) as unique_visitors
        FROM website_traffic
        WHERE visited_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
        GROUP BY DATE(visited_at)
        ORDER BY date ASC
    ", [$days]);

    $dayData = [];
    if ($data->count() > 0) {
        foreach ($data->results() as $row) {
            $dayData[$row->date] = [
                'visits' => (int)$row->visits,
                'unique' => (int)$row->unique_visitors
            ];
        }
    }

    $labels = [];
    $visits = [];
    $unique = [];

    for ($i = $days - 1; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-{$i} days"));
        $labels[] = date('D d', strtotime($date));
        $visits[] = $dayData[$date]['visits'] ?? 0;
        $unique[] = $dayData[$date]['unique'] ?? 0;
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'labels' => $labels,
            'visits' => $visits,
            'unique_visitors' => $unique
        ]
    ]);
}

/**
 * Get weekly statistics for the last 4-12 weeks
 */
function getWeeklyStats($db) {
    $weeks = (int)($_GET['weeks'] ?? 4);
    $weeks = min(max($weeks, 1), 52); // Limit to 1-52 weeks

    $data = $db->query("
        SELECT
            YEARWEEK(visited_at, 1) as yearweek,
            MIN(DATE(visited_at)) as week_start,
            COUNT(*) as visits,
            COUNT(DISTINCT session_id) as unique_visitors
        FROM website_traffic
        WHERE visited_at >= DATE_SUB(CURDATE(), INTERVAL ? WEEK)
        GROUP BY YEARWEEK(visited_at, 1)
        ORDER BY yearweek ASC
    ", [$weeks]);

    $labels = [];
    $visits = [];
    $unique = [];

    if ($data->count() > 0) {
        foreach ($data->results() as $row) {
            $labels[] = 'Uke ' . date('W', strtotime($row->week_start));
            $visits[] = (int)$row->visits;
            $unique[] = (int)$row->unique_visitors;
        }
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'labels' => $labels,
            'visits' => $visits,
            'unique_visitors' => $unique
        ]
    ]);
}

/**
 * Get monthly statistics for the last 12 months
 */
function getMonthlyStats($db) {
    $months = (int)($_GET['months'] ?? 12);
    $months = min(max($months, 1), 24); // Limit to 1-24 months

    $data = $db->query("
        SELECT
            DATE_FORMAT(visited_at, '%Y-%m') as month,
            COUNT(*) as visits,
            COUNT(DISTINCT session_id) as unique_visitors
        FROM website_traffic
        WHERE visited_at >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
        GROUP BY DATE_FORMAT(visited_at, '%Y-%m')
        ORDER BY month ASC
    ", [$months]);

    $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Des'];

    $labels = [];
    $visits = [];
    $unique = [];

    if ($data->count() > 0) {
        foreach ($data->results() as $row) {
            $monthNum = (int)date('n', strtotime($row->month . '-01'));
            $labels[] = $monthNames[$monthNum - 1];
            $visits[] = (int)$row->visits;
            $unique[] = (int)$row->unique_visitors;
        }
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'labels' => $labels,
            'visits' => $visits,
            'unique_visitors' => $unique
        ]
    ]);
}

/**
 * Get top pages by visits
 */
function getTopPages($db) {
    $period = $_GET['period'] ?? 'week';
    $limit = (int)($_GET['limit'] ?? 10);
    $limit = min(max($limit, 1), 50);

    $intervals = [
        'today' => 'DATE(visited_at) = CURDATE()',
        'week' => 'visited_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)',
        'month' => 'visited_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)'
    ];

    $where = $intervals[$period] ?? $intervals['week'];

    $data = $db->query("
        SELECT
            page_url,
            page_title,
            COUNT(*) as visits,
            COUNT(DISTINCT session_id) as unique_visitors
        FROM website_traffic
        WHERE {$where}
        GROUP BY page_url, page_title
        ORDER BY visits DESC
        LIMIT {$limit}
    ");

    $pages = [];
    if ($data->count() > 0) {
        foreach ($data->results() as $row) {
            $pages[] = [
                'url' => $row->page_url,
                'title' => $row->page_title ?: $row->page_url,
                'visits' => (int)$row->visits,
                'unique_visitors' => (int)$row->unique_visitors
            ];
        }
    }

    echo json_encode([
        'success' => true,
        'data' => $pages
    ]);
}

/**
 * Get device statistics
 */
function getDeviceStats($db) {
    $period = $_GET['period'] ?? 'week';

    $intervals = [
        'today' => 'DATE(visited_at) = CURDATE()',
        'week' => 'visited_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)',
        'month' => 'visited_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)'
    ];

    $where = $intervals[$period] ?? $intervals['week'];

    $data = $db->query("
        SELECT
            device_type,
            COUNT(*) as visits
        FROM website_traffic
        WHERE {$where}
        GROUP BY device_type
        ORDER BY visits DESC
    ");

    $devices = [];
    if ($data->count() > 0) {
        foreach ($data->results() as $row) {
            $devices[] = [
                'device' => ucfirst($row->device_type),
                'visits' => (int)$row->visits
            ];
        }
    }

    echo json_encode([
        'success' => true,
        'data' => $devices
    ]);
}

/**
 * Get browser statistics
 */
function getBrowserStats($db) {
    $period = $_GET['period'] ?? 'week';

    $intervals = [
        'today' => 'DATE(visited_at) = CURDATE()',
        'week' => 'visited_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)',
        'month' => 'visited_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)'
    ];

    $where = $intervals[$period] ?? $intervals['week'];

    $data = $db->query("
        SELECT
            browser,
            COUNT(*) as visits
        FROM website_traffic
        WHERE {$where}
        GROUP BY browser
        ORDER BY visits DESC
    ");

    $browsers = [];
    if ($data->count() > 0) {
        foreach ($data->results() as $row) {
            $browsers[] = [
                'browser' => $row->browser,
                'visits' => (int)$row->visits
            ];
        }
    }

    echo json_encode([
        'success' => true,
        'data' => $browsers
    ]);
}
?>
