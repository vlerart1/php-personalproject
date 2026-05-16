<?php
// admin/hotels.php
$page_title = 'Manage Hotels';
require_once 'includes/header.php';

$message = '';
$error = '';

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add' || $_POST['action'] === 'edit') {
            $name = sanitize($_POST['name']);
            $city = sanitize($_POST['city']);
            $country = sanitize($_POST['country']);
            $rating = (int)$_POST['rating'];
            $description = sanitize($_POST['description']);
            $address = sanitize($_POST['address']);
            $status = $_POST['status'];
            $image_url = sanitize($_POST['image_url']);

            if ($_POST['action'] === 'add') {
                $stmt = $pdo->prepare("INSERT INTO hotels (name, description, address, city, country, star_rating, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $description, $address, $city, $country, $rating, $status]);
                $hotel_id = $pdo->lastInsertId();
                
                if ($image_url) {
                    $pdo->prepare("INSERT INTO hotel_images (hotel_id, image_path, is_primary) VALUES (?, ?, 1)")->execute([$hotel_id, $image_url]);
                }
                $message = "Hotel added successfully!";
            } else {
                $id = (int)$_POST['id'];
                $stmt = $pdo->prepare("UPDATE hotels SET name=?, description=?, address=?, city=?, country=?, star_rating=?, status=? WHERE id=?");
                $stmt->execute([$name, $description, $address, $city, $country, $rating, $status, $id]);
                
                if ($image_url) {
                    $pdo->prepare("UPDATE hotel_images SET image_path=? WHERE hotel_id=? AND is_primary=1")->execute([$image_url, $id]);
                }
                $message = "Hotel updated successfully!";
            }
        }
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM hotels WHERE id = ?")->execute([$id]);
    $message = "Hotel deleted successfully!";
}

// Fetch all hotels
$hotels = $pdo->query("
    SELECT h.*, (SELECT image_path FROM hotel_images hi WHERE hi.hotel_id = h.id AND hi.is_primary = 1 LIMIT 1) as image 
    FROM hotels h 
    ORDER BY h.id DESC
")->fetchAll();

$editing_hotel = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    foreach ($hotels as $h) {
        if ($h['id'] == $id) {
            $editing_hotel = $h;
            break;
        }
    }
}
?>

<?php if ($message): ?>
    <script>document.addEventListener('DOMContentLoaded', () => showToast("<?php echo $message; ?>", 'success'));</script>
<?php endif; ?>

<div style="margin-bottom: 24px; display: flex; justify-content: flex-end;">
    <button onclick="toggleForm('add')" class="btn btn-primary"><i class="fas fa-plus"></i> Add New Hotel</button>
</div>

<!-- Form Section (Hidden by default unless editing or adding) -->
<div id="hotelForm" class="card" style="display: <?php echo $editing_hotel ? 'block' : 'none'; ?>; margin-bottom: 32px;">
    <div class="card-header">
        <h2 id="formTitle"><?php echo $editing_hotel ? 'Edit Hotel' : 'Add New Hotel'; ?></h2>
        <button onclick="toggleForm()" class="btn btn-sm btn-danger"><i class="fas fa-times"></i></button>
    </div>
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="action" id="formAction" value="<?php echo $editing_hotel ? 'edit' : 'add'; ?>">
            <input type="hidden" name="id" id="hotelId" value="<?php echo $editing_hotel['id'] ?? ''; ?>">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
                <div class="form-group">
                    <label>Hotel Name</label>
                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($editing_hotel['name'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label>Image URL</label>
                    <input type="url" name="image_url" class="form-control" value="<?php echo htmlspecialchars($editing_hotel['image'] ?? ''); ?>" placeholder="https://unsplash.com/...">
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 24px;">
                <div class="form-group">
                    <label>City</label>
                    <input type="text" name="city" class="form-control" value="<?php echo htmlspecialchars($editing_hotel['city'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label>Country</label>
                    <input type="text" name="country" class="form-control" value="<?php echo htmlspecialchars($editing_hotel['country'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label>Rating</label>
                    <select name="rating" class="form-control">
                        <?php for($i=5; $i>=1; $i--): ?>
                            <option value="<?php echo $i; ?>" <?php echo (isset($editing_hotel['star_rating']) && $editing_hotel['star_rating'] == $i) ? 'selected' : ''; ?>><?php echo $i; ?> Stars</option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Address</label>
                <input type="text" name="address" class="form-control" value="<?php echo htmlspecialchars($editing_hotel['address'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="4" required><?php echo htmlspecialchars($editing_hotel['description'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label>Status</label>
                <select name="status" class="form-control">
                    <option value="active" <?php echo (isset($editing_hotel['status']) && $editing_hotel['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo (isset($editing_hotel['status']) && $editing_hotel['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
            
            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <button type="button" onclick="toggleForm()" class="btn">Cancel</button>
                <button type="submit" class="btn btn-primary"><?php echo $editing_hotel ? 'Update Hotel' : 'Save Hotel'; ?></button>
            </div>
        </form>
    </div>
</div>

<!-- Hotels List -->
<div class="card">
    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th>Hotel</th>
                    <th>Location</th>
                    <th>Rating</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($hotels as $hotel): ?>
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <img src="<?php echo htmlspecialchars($hotel['image'] ?? 'https://via.placeholder.com/100'); ?>" style="width: 40px; height: 40px; border-radius: 8px; object-fit: cover;">
                                <div style="font-weight: 600;"><?php echo htmlspecialchars($hotel['name']); ?></div>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($hotel['city'] . ', ' . $hotel['country']); ?></td>
                        <td><div style="color: var(--accent);"><i class="fas fa-star"></i> <?php echo $hotel['star_rating']; ?>.0</div></td>
                        <td><span class="badge <?php echo $hotel['status']; ?>"><?php echo $hotel['status']; ?></span></td>
                        <td>
                            <div style="display: flex; gap: 8px;">
                                <a href="?edit=<?php echo $hotel['id']; ?>" class="btn btn-sm" title="Edit"><i class="fas fa-edit"></i></a>
                                <a href="?delete=<?php echo $hotel['id']; ?>" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this hotel?')"><i class="fas fa-trash"></i></a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function toggleForm(action = null) {
        const form = document.getElementById('hotelForm');
        if (!action && form.style.display === 'none') return;
        
        if (action === 'add') {
            document.getElementById('formTitle').innerText = 'Add New Hotel';
            document.getElementById('formAction').value = 'add';
            document.getElementById('hotelId').value = '';
            document.querySelector('#hotelForm form').reset();
            form.style.display = 'block';
        } else if (!action) {
            form.style.display = 'none';
        } else {
            form.style.display = 'block';
        }
    }
</script>

<?php require_once 'includes/footer.php'; ?>
