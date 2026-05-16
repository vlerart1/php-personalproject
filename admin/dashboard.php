<?php
// admin/dashboard.php
$page_title = 'Dashboard Overview';
require_once 'includes/header.php';

// Fetch stats
$stats = [];
$stats['hotels'] = $pdo->query("SELECT COUNT(*) FROM hotels")->fetchColumn();
$stats['users'] = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$stats['bookings'] = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
$stats['rooms'] = $pdo->query("SELECT SUM(quantity) FROM room_types")->fetchColumn() ?? 0;
$stats['revenue'] = $pdo->query("SELECT SUM(total_price) FROM bookings WHERE status = 'completed'")->fetchColumn() ?? 0;

// Fetch chart data (Revenue by month for last 6 months)
$chart_query = "
    SELECT DATE_FORMAT(created_at, '%b') as month, SUM(total_price) as total 
    FROM bookings 
    WHERE status = 'completed' AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY month 
    ORDER BY MIN(created_at)
";
$chart_data = $pdo->query($chart_query)->fetchAll(PDO::FETCH_KEY_PAIR);

// Fallback if no data
if (empty($chart_data)) {
    $chart_data = [date('M') => 0];
}
$labels = array_keys($chart_data);
$data_points = array_values($chart_data);

// Fetch recent bookings
$recent_bookings = $pdo->query("
    SELECT b.id, b.total_price, b.status, b.created_at, u.first_name, u.last_name, h.name as hotel_name 
    FROM bookings b 
    JOIN users u ON b.user_id = u.id 
    JOIN hotels h ON b.hotel_id = h.id 
    ORDER BY b.created_at DESC LIMIT 6
")->fetchAll();
?>

<div class="kpi-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 24px; margin-bottom: 32px;">
    <div class="card" style="padding: 24px; display: flex; align-items: center; gap: 16px; margin-bottom: 0;">
        <div style="width: 48px; height: 48px; background: #eff6ff; color: #3b82f6; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;"><i class="fas fa-hotel"></i></div>
        <div>
            <p style="color: var(--text-muted); font-size: 0.85rem; font-weight: 500;">Hotels</p>
            <h3 style="font-size: 1.5rem; font-weight: 700;"><?php echo number_format($stats['hotels']); ?></h3>
        </div>
    </div>
    <div class="card" style="padding: 24px; display: flex; align-items: center; gap: 16px; margin-bottom: 0;">
        <div style="width: 48px; height: 48px; background: #f0fdf4; color: #10b981; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;"><i class="fas fa-users"></i></div>
        <div>
            <p style="color: var(--text-muted); font-size: 0.85rem; font-weight: 500;">Users</p>
            <h3 style="font-size: 1.5rem; font-weight: 700;"><?php echo number_format($stats['users']); ?></h3>
        </div>
    </div>
    <div class="card" style="padding: 24px; display: flex; align-items: center; gap: 16px; margin-bottom: 0;">
        <div style="width: 48px; height: 48px; background: #fefce8; color: #eab308; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;"><i class="fas fa-calendar-check"></i></div>
        <div>
            <p style="color: var(--text-muted); font-size: 0.85rem; font-weight: 500;">Bookings</p>
            <h3 style="font-size: 1.5rem; font-weight: 700;"><?php echo number_format($stats['bookings']); ?></h3>
        </div>
    </div>
    <div class="card" style="padding: 24px; display: flex; align-items: center; gap: 16px; margin-bottom: 0;">
        <div style="width: 48px; height: 48px; background: #faf5ff; color: #a855f7; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;"><i class="fas fa-dollar-sign"></i></div>
        <div>
            <p style="color: var(--text-muted); font-size: 0.85rem; font-weight: 500;">Revenue</p>
            <h3 style="font-size: 1.5rem; font-weight: 700;"><?php echo format_price($stats['revenue']); ?></h3>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
    <div class="card">
        <div class="card-header">
            <h2>Recent Bookings</h2>
            <a href="bookings.php" style="color: var(--sidebar-bg); font-size: 0.9rem; font-weight: 500; text-decoration: none;">View All</a>
        </div>
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Guest</th>
                        <th>Hotel</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($recent_bookings as $booking): ?>
                        <tr>
                            <td style="font-weight: 500;">#<?php echo $booking['id']; ?></td>
                            <td>
                                <div style="font-weight: 500; color: var(--text-main);"><?php echo htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']); ?></div>
                                <div style="font-size: 0.8rem; color: var(--text-muted);"><?php echo date('M j, Y', strtotime($booking['created_at'])); ?></div>
                            </td>
                            <td><?php echo htmlspecialchars($booking['hotel_name']); ?></td>
                            <td style="font-weight: 600;"><?php echo format_price($booking['total_price']); ?></td>
                            <td><span class="badge <?php echo $booking['status']; ?>"><?php echo $booking['status']; ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h2>Revenue Trend</h2>
        </div>
        <div class="card-body">
            <canvas id="revenueChart" width="100" height="100"></canvas>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('revenueChart').getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(212, 175, 55, 0.4)');
        gradient.addColorStop(1, 'rgba(212, 175, 55, 0.0)');
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($labels); ?>,
                datasets: [{
                    label: 'Revenue',
                    data: <?php echo json_encode($data_points); ?>,
                    borderColor: '#d4af37',
                    backgroundColor: gradient,
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#d4af37',
                    pointBorderWidth: 2,
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { borderDash: [2, 4], color: '#e5e7eb' }, border: { display: false } },
                    x: { grid: { display: false }, border: { display: false } }
                }
            }
        });
    });
</script>

<?php require_once 'includes/footer.php'; ?>
