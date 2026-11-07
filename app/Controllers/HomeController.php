<?php
declare(strict_types=1);

final class HomeController
{
    public function index(): void
    {
        $categories = DB::all(
            "SELECT c.*, (SELECT COUNT(*) FROM provider_profiles pp JOIN users u ON u.id = pp.user_id
                          WHERE pp.category_id = c.id AND u.status = 'active') AS providers
             FROM categories c WHERE c.is_active = 1 ORDER BY c.sort_order, c.name"
        );
        $city = current_city();
        $featured = ProviderController::query(['sort' => 'recommended', 'verified' => '1', 'near' => $city], 8, 0)['rows'];
        $jobs = DB::all(
            "SELECT j.*, c.name AS category_name, c.icon AS category_icon, c.slug AS category_slug
             FROM jobs j LEFT JOIN categories c ON c.id = j.category_id
             WHERE j.status = 'open' ORDER BY j.created_at DESC LIMIT 4"
        );
        $reviews = DB::all(
            "SELECT r.*, c.first_name, c.last_name, c.id AS client_uid, c.avatar, c.city,
                    p.first_name AS p_first, p.last_name AS p_last, cat.name AS category_name
             FROM reviews r
             JOIN users c ON c.id = r.client_id
             JOIN users p ON p.id = r.provider_id
             LEFT JOIN provider_profiles pp ON pp.user_id = p.id
             LEFT JOIN categories cat ON cat.id = pp.category_id
             WHERE r.is_hidden = 0 AND r.rating = 5 AND CHAR_LENGTH(r.comment) > 60
             ORDER BY r.created_at DESC LIMIT 3"
        );
        // Prefer the chosen city, then fill missing spotlight trades from nearby cities.
        $spotlightTrades = ['aircon', 'cleaning', 'plumbing'];
        $popular = ServiceController::query(['city' => $city, 'sort' => 'recommended'], 40, 0)['rows'];
        $candidates = array_column($popular, null, 'id');
        if (count($popular) < 3 || array_diff($spotlightTrades, array_column($popular, 'category_slug'))) {
            $nearby = ServiceController::query(['near' => $city, 'sort' => 'recommended'], 40, 0)['rows'];
            $candidates += array_column($nearby, null, 'id');
        }
        $discovery = [];
        foreach ($spotlightTrades as $slug) {
            foreach ($candidates as $id => $service) {
                if ($service['category_slug'] === $slug) {
                    $discovery[$id] = $service;
                    break;
                }
            }
        }
        $popular = array_slice(array_values($discovery + $candidates), 0, 3);
        $popularInCity = !array_filter($popular, fn($service) => $service['city'] !== $city);

        // The signed-in person's next booking, shown beside the banner on computers
        $next = null;
        $pendingRequests = 0;
        $reminders = [];
        if ($u = user()) {
            $mine = is_role('provider') ? 'b.provider_id' : 'b.client_id';
            $other = is_role('provider') ? 'b.client_id' : 'b.provider_id';
            if (!is_role('admin')) {
                $next = DB::one(
                    "SELECT b.*, o.first_name, o.last_name, o.id AS other_id, o.avatar,
                            pp.verification_status AS other_verification_status
                     FROM bookings b JOIN users o ON o.id = $other
                     LEFT JOIN provider_profiles pp ON pp.user_id = b.provider_id
                     WHERE $mine = ? AND b.status IN ('requested','accepted','in_progress')
                       AND (b.scheduled_date IS NULL OR b.scheduled_date >= CURDATE())
                     ORDER BY b.scheduled_date IS NULL, b.scheduled_date, b.status = 'in_progress' DESC, b.scheduled_time LIMIT 1",
                    [(int) $u['id']]
                );
            }
            if (is_role('provider')) {
                $pendingRequests = (int) DB::value("SELECT COUNT(*) FROM bookings WHERE provider_id = ? AND status = 'requested'", [(int) $u['id']]);
            }
            // Things a customer should act on: jobs whose day has passed, payments due, then reviews
            if (is_role('client')) {
                foreach (DB::all(
                    "SELECT b.id, b.title, b.scheduled_date, p.first_name AS p_first FROM bookings b JOIN users p ON p.id = b.provider_id
                     WHERE b.client_id = ? AND b.status IN ('accepted','in_progress') AND b.scheduled_date < CURDATE()
                     ORDER BY b.scheduled_date DESC LIMIT 1",
                    [(int) $u['id']]
                ) as $b) {
                    $reminders[] = ['overdue', '/bookings/' . $b['id'], 'Did this job happen?', '“' . $b['title'] . '” with ' . $b['p_first'] . ' was booked for ' . fmt_date($b['scheduled_date'], 'M j')];
                }
                foreach (DB::all(
                    "SELECT b.id, b.title, b.amount, p.first_name AS p_first FROM bookings b JOIN users p ON p.id = b.provider_id
                     WHERE b.client_id = ? AND b.status = 'completed' AND b.payment_status = 'unpaid' ORDER BY b.completed_at DESC LIMIT 2",
                    [(int) $u['id']]
                ) as $b) {
                    $reminders[] = ['pay', '/bookings/' . $b['id'] . '#payment', 'Payment due', money($b['amount']) . ' to ' . $b['p_first'] . ' for “' . $b['title'] . '”'];
                }
                foreach (DB::all(
                    "SELECT b.id, b.title, p.first_name AS p_first FROM bookings b JOIN users p ON p.id = b.provider_id
                     LEFT JOIN reviews r ON r.booking_id = b.id
                     WHERE b.client_id = ? AND b.status = 'completed' AND r.id IS NULL ORDER BY b.completed_at DESC LIMIT 2",
                    [(int) $u['id']]
                ) as $b) {
                    $reminders[] = ['review', '/bookings/' . $b['id'] . '#review', 'How did ' . $b['p_first'] . ' do?', 'Leave a review for “' . $b['title'] . '”'];
                }
                $reminders = array_slice($reminders, 0, 2);
            }
        }

        view('home/index', compact('categories', 'featured', 'jobs', 'reviews', 'popular', 'popularInCity', 'next', 'pendingRequests', 'city', 'reminders')
            + ['bodyClass' => 'page-home']);
    }

    public function howItWorks(): void
    {
        view('home/how', ['title' => 'How it works']);
    }
}
