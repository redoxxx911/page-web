<?php
// admin.php
session_start();
require_once 'config.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['username'] !== 'adm') {
    header("Location: login.php");
    exit();
}

try {
    // Fetch total number of users
    $stmt = $pdo->query("SELECT COUNT(*) AS total_users FROM users");
    $total_users = $stmt->fetch()['total_users'];

    // Fetch total number of foods
    $stmt = $pdo->query("SELECT COUNT(*) AS total_foods FROM foods");
    $total_foods = $stmt->fetch()['total_foods'];

    // Fetch recent users (for example, last 5 users)
    $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");
    $recent_users = $stmt->fetchAll();

    // Fetch recent food items (for example, last 5 food items)
    $stmt = $pdo->query("SELECT * FROM foods ");
    $food_items = $stmt->fetchAll();
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

$stmt = $pdo->prepare("SELECT DATE(created_at) as sign_up_date, COUNT(*) as user_count FROM users GROUP BY sign_up_date ORDER BY sign_up_date DESC");
$stmt->execute();
$user_data = $stmt->fetchAll();

// Prepare the data for the graph
$dates = [];
$user_counts = [];
foreach ($user_data as $row) {
    $dates[] = $row['sign_up_date'];
    $user_counts[] = $row['user_count'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NutriTrack Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #4F46E5;
            --surface: #FFFFFF;
            --background: #F3F4F6;
            --text-primary: #111827;
            --text-secondary: #6B7280;
            --accent: #f9ac54;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        body {
            background: var(--background);
            min-height: 100vh;
            display: flex;
        }

        .sidebar {
            width: 280px;
            height: 100vh;
            background: var(--surface);
            padding: 2rem;
            position: fixed;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
        }

        .main-content {
            flex: 1;
            margin-left: 50px;
            padding: 2rem;
        }

        .header {
            background: var(--surface);
            padding: 1.5rem 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .admin-controls {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s ease;
            background: var(--primary);
            color: white;
            text-decoration: none;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--surface);
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            transition: transform 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
        }

        h1 {
            font-size: 1.875rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        h3 {
            color: var(--text-secondary);
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.5rem;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 600;
            color: var(--primary);
        }

        .error {
            color: #dc2626;
            background: #fee2e2;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-size: 0.875rem;
            border-left: 4px solid #dc2626;
        }

        .data-table {
            background: var(--surface);
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            overflow: hidden;
        }

        .data-table h2 {
            color: var(--text-primary);
            font-size: 1.25rem;
            margin-bottom: 1.5rem;
            font-weight: 600;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
        }

        th {
            text-align: left;
            padding: 1rem;
            background: #f8fafc;
            color: var(--text-secondary);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-size: 0.75rem;
            border-bottom: 2px solid #e5e7eb;
        }

        td {
            padding: 1rem;
            border-bottom: 1px solid #e5e7eb;
            color: var(--text-primary);
        }

        tr:hover {
            background: #f8fafc;
        }

        tr:last-child td {
            border-bottom: none;
        }

        .action-btn {
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-right: 0.5rem;
        }

        .edit-btn {
            background: #4F46E5;
            color: white;
        }

        .delete-btn {
            background: #fee2e2;
            color: #dc2626;
        }

        .edit-btn:hover {
            background: #4338ca;
        }

        .delete-btn:hover {
            background: #fecaca;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
            background: #ecfdf5;
            color: #059669;
        }

        .chart-container {
            margin-top: 2rem;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>

    <div class="main-content">
        <div class="header">
            <h1>Dashboard Overview</h1>
            <div class="admin-controls">
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="logout.php" class="btn">Logout</a>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Users</h3>
                <p class="stat-number"><?php echo $total_users; ?></p>
            </div>
            <div class="stat-card">
                <h3>Food Items</h3>
                <p class="stat-number"><?php echo $total_foods; ?></p>
            </div>
        </div>

        <div class="data-table">
            <h2>Recent Users</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_users as $user): ?>
                    <tr>
                        <td>#<?php echo htmlspecialchars($user['id']); ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                        <td>
                            <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="action-btn edit-btn">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <button onclick="deleteUser(<?php echo $user['id']; ?>)" class="action-btn delete-btn">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="data-table">
            <h2>Food Database</h2>
             <a href="add_food.php" class="btn" style="margin-bottom: 1rem;">Add New Food</a>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Calories</th>
                        <th>Protein</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($food_items as $food): ?>
                    <tr>
                        <td>#<?php echo htmlspecialchars($food['id']); ?></td>
                        <td><?php echo htmlspecialchars($food['name']); ?></td>
                        <td><?php echo htmlspecialchars($food['calories']); ?> kcal</td>
                        <td><?php echo htmlspecialchars($food['protein']); ?> g</td>
                        <td>
                            <button onclick="deleteFood(<?php echo $food['id']; ?>)" class="action-btn delete-btn">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="chart-container">
            <canvas id="userSignUpChart" width="400" height="200"></canvas>
        </div>
    </div>

    <script>
  function deleteUser(id) {
    if (confirm('Are you sure you want to delete this user?')) {
        fetch('delete_user.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: id })
        })
        .then(response => response.json())
        .then(data => {
            console.log(data); // Log server response
            if (data.success) {
                alert('User deleted successfully.');
                location.reload();
            } else {
                alert('Failed to delete user: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
}

    function deleteFood(id) {
        if (confirm('Are you sure you want to delete this food item?')) {
            fetch('delete_food.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({id: id})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
        }
    }
        var ctx = document.getElementById('userSignUpChart').getContext('2d');
        var userSignUpChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($dates); ?>,
                datasets: [{
                    label: 'User Sign Ups',
                    data: <?php echo json_encode($user_counts); ?>,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Number of Users'
                        }
                    }
                }
            }
        });
        
    </script>

</body>
</html>