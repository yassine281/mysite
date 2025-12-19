<?php
include 'api/config.php';

$city = $_GET['city'] ?? '';
$check_in = $_GET['check_in'] ?? '';
$check_out = $_GET['check_out'] ?? '';
$guests = $_GET['guests'] ?? 1;

// Search hotels
$sql = "SELECT * FROM hotels WHERE city LIKE '%$city%' OR name LIKE '%$city%'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - HotelBook</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
    <style>
        .results-container {
            max-width: 1200px;
            margin: 3rem auto;
            padding: 0 2rem;
        }
        .search-info {
            background: var(--light-color);
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }
        .hotel-list {
            display: grid;
            gap: 2rem;
        }
        .hotel-item {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: all 0.3s ease;
        }
        .hotel-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        .hotel-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }
        .hotel-details {
            padding: 1.5rem;
        }
        .hotel-name {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: var(--dark-color);
        }
        .hotel-rating {
            color: #ffa500;
            margin-bottom: 1rem;
        }
        .hotel-address {
            color: #666;
            margin-bottom: 1rem;
        }
        .hotel-amenities {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }
        .amenity {
            background: var(--light-color);
            padding: 0.3rem 0.8rem;
            border-radius: 5px;
            font-size: 0.9rem;
        }
        .hotel-price {
            font-size: 1.8rem;
            color: var(--primary-color);
            font-weight: bold;
            margin-bottom: 1rem;
        }
        .btn-view-hotel {
            display: inline-block;
            background: var(--primary-color);
            color: white;
            padding: 0.8rem 2rem;
            border-radius: 5px;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .btn-view-hotel:hover {
            background: #ff5252;
        }
        @media (max-width: 768px) {
            .hotel-item {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="container">
            <a href="index.html" class="logo">🏨 HotelBook</a>
            <ul class="nav-links">
                <li><a href="index.html">Home</a></li>
                <li><a href="about.html">About</a></li>
                <li><a href="contact.html">Contact</a></li>
                <li><a href="login.html">Login</a></li>
            </ul>
        </div>
    </nav>

    <div class="results-container">
        <div class="search-info">
            <h2>Search Results for "<?php echo htmlspecialchars($city); ?>"</h2>
            <p>Check-in: <?php echo htmlspecialchars($check_in); ?> | Check-out: <?php echo htmlspecialchars($check_out); ?> | Guests: <?php echo htmlspecialchars($guests); ?></p>
        </div>

        <div class="hotel-list">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($hotel = $result->fetch_assoc()): ?>
                    <div class="hotel-item">
                        <img src="<?php echo $hotel['image_url'] ?: 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=400'; ?>" 
                             alt="<?php echo htmlspecialchars($hotel['name']); ?>" 
                             class="hotel-image">
                        
                        <div class="hotel-details">
                            <h3 class="hotel-name"><?php echo htmlspecialchars($hotel['name']); ?></h3>
                            <div class="hotel-rating">
                                ⭐ <?php echo $hotel['rating']; ?>/5.0
                            </div>
                            <p class="hotel-address">📍 <?php echo htmlspecialchars($hotel['address']); ?></p>
                            
                            <?php if ($hotel['amenities']): ?>
                                <div class="hotel-amenities">
                                    <?php 
                                    $amenities = explode(',', $hotel['amenities']);
                                    foreach (array_slice($amenities, 0, 4) as $amenity): 
                                    ?>
                                        <span class="amenity"><?php echo trim($amenity); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="hotel-price">
                                Starting from $120/night
                            </div>
                            
                            <a href="hotel-details.php?id=<?php echo $hotel['hotel_id']; ?>&check_in=<?php echo $check_in; ?>&check_out=<?php echo $check_out; ?>&guests=<?php echo $guests; ?>" 
                               class="btn-view-hotel">View Details & Book</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="text-align: center; padding: 3rem;">
                    <h3>No hotels found</h3>
                    <p>Try searching for a different location or adjusting your dates.</p>
                    <a href="index.html" class="btn-view-hotel" style="margin-top: 1rem;">Back to Home</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="js/script.js"></script>
</body>
</html>
