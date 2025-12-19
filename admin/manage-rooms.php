<?php
session_start();
require_once '../config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.html');
    exit();
}

// Handle Delete Room
if (isset($_GET['delete'])) {
    $room_id = intval($_GET['delete']);
    $delete_sql = "DELETE FROM rooms WHERE id = $room_id";
    
    if ($conn->query($delete_sql)) {
        $success = "Room deleted successfully!";
    } else {
        $error = "Error deleting room: " . $conn->error;
    }
}

// Handle Add/Edit Room
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_id = isset($_POST['room_id']) ? intval($_POST['room_id']) : 0;
    $hotel_id = intval($_POST['hotel_id']);
    $room_type = $conn->real_escape_string($_POST['room_type']);
    $price = floatval($_POST['price']);
    $capacity = intval($_POST['capacity']);
    $description = $conn->real_escape_string($_POST['description']);
    $amenities = $conn->real_escape_string($_POST['amenities']);
    $total_rooms = intval($_POST['total_rooms']);
    $available_rooms = intval($_POST['available_rooms']);
    
    if ($room_id > 0) {
        // Update existing room
        $sql = "UPDATE rooms SET 
                hotel_id = $hotel_id,
                room_type = '$room_type',
                price = $price,
                capacity = $capacity,
                description = '$description',
                amenities = '$amenities',
                total_rooms = $total_rooms,
                available_rooms = $available_rooms
                WHERE id = $room_id";
        $message = "Room updated successfully!";
    } else {
        // Add new room
        $sql = "INSERT INTO rooms (hotel_id, room_type, price, capacity, description, amenities, total_rooms, available_rooms) 
                VALUES ($hotel_id, '$room_type', $price, $capacity, '$description', '$amenities', $total_rooms, $available_rooms)";
        $message = "Room added successfully!";
    }
    
    if ($conn->query($sql)) {
        $success = $message;
    } else {
        $error = "Error: " . $conn->error;
    }
}

// Get all hotels for dropdown
$hotels_query = "SELECT id, name FROM hotels ORDER BY name";
$hotels_result = $conn->query($hotels_query);

// Get all rooms with hotel information
$rooms_query = "SELECT r.*, h.name as hotel_name 
                FROM rooms r 
                LEFT JOIN hotels h ON r.hotel_id = h.id 
                ORDER BY h.name, r.room_type";
$rooms_result = $conn->query($rooms_query);

// Get room for editing
$edit_room = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $edit_query = "SELECT * FROM rooms WHERE id = $edit_id";
    $edit_result = $conn->query($edit_query);
    $edit_room = $edit_result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Rooms - Admin Panel</title>
    <link rel="stylesheet" href="../css/admin.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background: #2c3e50;
            color: white;
            padding: 2rem 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar h2 {
            padding: 0 1.5rem;
            margin-bottom: 2rem;
            color: #3498db;
        }

        .sidebar ul {
            list-style: none;
        }

        .sidebar ul li {
            margin: 0.5rem 0;
        }

        .sidebar ul li a {
            display: block;
            padding: 1rem 1.5rem;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
        }

        .sidebar ul li a:hover,
        .sidebar ul li a.active {
            background: #34495e;
            border-left: 4px solid #3498db;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 2rem;
        }

        .header {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            color: #2c3e50;
        }

        .logout-btn {
            background: #e74c3c;
            color: white;
            padding: 0.7rem 1.5rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.3s;
        }

        .logout-btn:hover {
            background: #c0392b;
        }

        /* Messages */
        .message {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1.5rem;
        }

        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Form Section */
        .form-section {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .form-section h2 {
            margin-bottom: 1.5rem;
            color: #2c3e50;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            margin-bottom: 0.5rem;
            color: #2c3e50;
            font-weight: 600;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            font-family: inherit;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .btn {
            padding: 0.8rem 2rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #3498db;
            color: white;
        }

        .btn-primary:hover {
            background: #2980b9;
        }

        .btn-secondary {
            background: #95a5a6;
            color: white;
        }

        .btn-secondary:hover {
            background: #7f8c8d;
        }

        /* Table Section */
        .table-section {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .table-section h2 {
            margin-bottom: 1.5rem;
            color: #2c3e50;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table thead {
            background: #2c3e50;
            color: white;
        }

        table th,
        table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        table tbody tr:hover {
            background: #f8f9fa;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn-small {
            padding: 0.4rem 0.8rem;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s;
        }

        .btn-edit {
            background: #3498db;
            color: white;
        }

        .btn-edit:hover {
            background: #2980b9;
        }

        .btn-delete {
            background: #e74c3c;
            color: white;
        }

        .btn-delete:hover {
            background: #c0392b;
        }

        .status-badge {
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .status-available {
            background: #d4edda;
            color: #155724;
        }

        .status-full {
            background: #f8d7da;
            color: #721c24;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }

            .main-content {
                margin-left: 200px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            table {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <h2>Admin Panel</h2>
            <ul>
                <li><a href="dashboard.php">📊 Dashboard</a></li>
                <li><a href="manage-hotels.php">🏨 Manage Hotels</a></li>
                <li><a href="manage-rooms.php" class="active">🛏️ Manage Rooms</a></li>
                <li><a href="manage-bookings.php">📅 Manage Bookings</a></li>
                <li><a href="users.php">👥 Manage Users</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="header">
                <h1>Manage Rooms</h1>
                <a href="../api/logout.php" class="logout-btn">Logout</a>
            </div>

            <?php if (isset($success)): ?>
                <div class="message success"><?php echo $success; ?></div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="message error"><?php echo $error; ?></div>
            <?php endif; ?>

            <!-- Add/Edit Room Form -->
            <div class="form-section">
                <h2><?php echo $edit_room ? 'Edit Room' : 'Add New Room'; ?></h2>
                <form method="POST" action="">
                    <input type="hidden" name="room_id" value="<?php echo $edit_room['id'] ?? ''; ?>">
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="hotel_id">Hotel *</label>
                            <select name="hotel_id" id="hotel_id" required>
                                <option value="">Select Hotel</option>
                                <?php while ($hotel = $hotels_result->fetch_assoc()): ?>
                                    <option value="<?php echo $hotel['id']; ?>" 
                                        <?php echo (isset($edit_room) && $edit_room['hotel_id'] == $hotel['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($hotel['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="room_type">Room Type *</label>
                            <input type="text" name="room_type" id="room_type" 
                                   value="<?php echo htmlspecialchars($edit_room['room_type'] ?? ''); ?>" 
                                   placeholder="e.g., Deluxe, Suite, Standard" required>
                        </div>

                        <div class="form-group">
                            <label for="price">Price per Night ($) *</label>
                            <input type="number" name="price" id="price" step="0.01" 
                                   value="<?php echo $edit_room['price'] ?? ''; ?>" 
                                   placeholder="99.99" required>
                        </div>

                        <div class="form-group">
                            <label for="capacity">Capacity (Guests) *</label>
                            <input type="number" name="capacity" id="capacity" 
                                   value="<?php echo $edit_room['capacity'] ?? ''; ?>" 
                                   placeholder="2" required>
                        </div>

                        <div class="form-group">
                            <label for="total_rooms">Total Rooms *</label>
                            <input type="number" name="total_rooms" id="total_rooms" 
                                   value="<?php echo $edit_room['total_rooms'] ?? ''; ?>" 
                                   placeholder="10" required>
                        </div>

                        <div class="form-group">
                            <label for="available_rooms">Available Rooms *</label>
                            <input type="number" name="available_rooms" id="available_rooms" 
                                   value="<?php echo $edit_room['available_rooms'] ?? ''; ?>" 
                                   placeholder="8" required>
                        </div>

                        <div class="form-group full-width">
                            <label for="amenities">Amenities (comma separated)</label>
                            <input type="text" name="amenities" id="amenities" 
                                   value="<?php echo htmlspecialchars($edit_room['amenities'] ?? ''); ?>" 
                                   placeholder="WiFi, TV, Mini Bar, Air Conditioning">
                        </div>

                        <div class="form-group full-width">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" 
                                      placeholder="Describe the room..."><?php echo htmlspecialchars($edit_room['description'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <div class="form-buttons">
                        <button type="submit" class="btn btn-primary">
                            <?php echo $edit_room ? 'Update Room' : 'Add Room'; ?>
                        </button>
                        <?php if ($edit_room): ?>
                            <a href="manage-rooms.php" class="btn btn-secondary">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Rooms Table -->
            <div class="table-section">
                <h2>All Rooms</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Hotel</th>
                            <th>Room Type</th>
                            <th>Price/Night</th>
                            <th>Capacity</th>
                            <th>Availability</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($rooms_result->num_rows > 0): ?>
                            <?php while ($room = $rooms_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $room['id']; ?></td>
                                    <td><?php echo htmlspecialchars($room['hotel_name']); ?></td>
                                    <td><?php echo htmlspecialchars($room['room_type']); ?></td>
                                    <td>$<?php echo number_format($room['price'], 2); ?></td>
                                    <td><?php echo $room['capacity']; ?> guests</td>
                                    <td><?php echo $room['available_rooms'] . '/' . $room['total_rooms']; ?></td>
                                    <td>
                                        <?php if ($room['available_rooms'] > 0): ?>
                                            <span class="status-badge status-available">Available</span>
                                        <?php else: ?>
                                            <span class="status-badge status-full">Full</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="?edit=<?php echo $room['id']; ?>" class="btn-small btn-edit">Edit</a>
                                            <a href="?delete=<?php echo $room['id']; ?>" 
                                               class="btn-small btn-delete" 
                                               onclick="return confirm('Are you sure you want to delete this room?')">Delete</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 2rem; color: #95a5a6;">
                                    No rooms found. Add your first room above!
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
