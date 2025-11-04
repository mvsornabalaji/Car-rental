<?php
session_start();
include '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "User not logged in";
    exit();
}

$user_id = $_SESSION['user_id'];
echo "<h3>Debug Information for User ID: $user_id</h3>";

// Check all bookings for this user
$all_bookings_query = "SELECT * FROM bookings1 WHERE user_id = $user_id";
$all_bookings_result = mysqli_query($conn, $all_bookings_query);

echo "<h4>All Bookings:</h4>";
echo "<table border='1'>";
echo "<tr><th>ID</th><th>Vehicle ID</th><th>Total Amount</th><th>Status</th><th>Booking Date</th></tr>";

$total_all = 0;
$total_confirmed_completed = 0;
$total_completed_only = 0;

while ($booking = mysqli_fetch_assoc($all_bookings_result)) {
    echo "<tr>";
    echo "<td>" . $booking['id'] . "</td>";
    echo "<td>" . $booking['vehicle_id'] . "</td>";
    echo "<td>₹" . number_format($booking['total_amount']) . "</td>";
    echo "<td>" . $booking['status'] . "</td>";
    echo "<td>" . $booking['booking_date'] . "</td>";
    echo "</tr>";
    
    $total_all += $booking['total_amount'];
    
    if (in_array($booking['status'], ['confirmed', 'completed'])) {
        $total_confirmed_completed += $booking['total_amount'];
    }
    
    if ($booking['status'] == 'completed') {
        $total_completed_only += $booking['total_amount'];
    }
}
echo "</table>";

echo "<h4>Totals:</h4>";
echo "<p>Total of all bookings: ₹" . number_format($total_all) . "</p>";
echo "<p>Total of confirmed + completed: ₹" . number_format($total_confirmed_completed) . "</p>";
echo "<p>Total of completed only: ₹" . number_format($total_completed_only) . "</p>";

// Test the queries
echo "<h4>Query Results:</h4>";

$total_spent_query = "SELECT SUM(total_amount) as total FROM bookings1 WHERE user_id = $user_id AND status IN ('confirmed', 'completed')";
$total_spent_result = mysqli_query($conn, $total_spent_query);
$total_spent = mysqli_fetch_assoc($total_spent_result)['total'] ?: 0;
echo "<p>Query result (confirmed + completed): ₹" . number_format($total_spent) . "</p>";

$total_spent_query2 = "SELECT SUM(total_amount) as total FROM bookings1 WHERE user_id = $user_id";
$total_spent_result2 = mysqli_query($conn, $total_spent_query2);
$total_spent2 = mysqli_fetch_assoc($total_spent_result2)['total'] ?: 0;
echo "<p>Query result (all bookings): ₹" . number_format($total_spent2) . "</p>";
?>

<a href="dashboard.php">Back to Dashboard</a>