<?php
// admin/rooms.php
$page_title = 'Manage Rooms';
require_once 'includes/header.php';

$message = '';

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $hotel_id = (int)$_POST['hotel_id'];
        $name = sanitize($_POST['name']);
        $description = sanitize($_POST['description']);
        $price = (float)$_POST['price'];
        $capacity = (int)$_POST['capacity'];
        $quantity = (int)$_POST['quantity'];

        if ($_POST['action'] === 'add') {
            $stmt = $pdo->prepare("INSERT INTO room_types (hotel_id, name, description, price_per_night, capacity, quantity) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$hotel_id, $name, $description, $price, $capacity, $quantity]);
            $message = "Room type added successfully!";
        } elseif ($_POST['action'] === 'edit') {
            $id = (int)$_POST['id'];
            $stmt = $pdo->prepare("UPDATE room_types SET name=?, description=?, price_per_night=?, capacity=?, quantity=? WHERE id=?");
            $stmt->execute([$name, $description, $price, $capacity, $quantity, $id]);
            $message = "Room type updated successfully!";
        }
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM room_types WHERE id = ?")->execute([$id]);
    $message = "Room type deleted successfully!";
}

// Fetch all hotels for filter/dropdown
$hotels = $pdo->query("SELECT id, name FROM hotels ORDER BY name")->fetchAll();

$selected_hotel_id = isset($_GET['hotel_id']) ? (int)$_GET['hotel_id'] : ($hotels[0]['id'] ?? 0);

// Fetch rooms for selected hotel
$rooms = [];
if ($selected_hotel_id) {
    $stmt = $pdo->prepare("SELECT * FROM room_types WHERE hotel_id = ? ORDER BY price_per_night ASC");
    $stmt->execute([$selected_hotel_id]);
    $rooms = $stmt->fetchAll();
}

$editing_room = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    foreach ($rooms as $r) {
        if ($r['id'] == $id) {
            $editing_room = $r;
            break;
        }
    }
}
?>

<?php if ($message): ?>
    <script>document.addEventListener('DOMContentLoaded', () => showToast("<?php echo $message; ?>", 'success'));</script>
<?php endif; ?>

<div class="card" style="margin-bottom: 24px;">
    <div class="card-body" style="display: flex; align-items: center; justify-content: space-between; padding: 16px 24px;">
        <form method="GET" style="display: flex; align-items: center; gap: 16px;">
            <label style="font-weight: 600; white-space: nowrap;">Select Hotel:</label>
            <select name="hotel_id" class="form-control" style="width: 300px; margin-bottom: 0;" onchange="this.form.submit()">
                <?php foreach($hotels as $h): ?>
                    <option value="<?php echo $h['id']; ?>" <?php echo $h['id'] == $selected_hotel_id ? 'selected' : ''; ?>><?php echo htmlspecialchars($h['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </form>
        <button onclick="toggleForm('add')" class="btn btn-primary"><i class="fas fa-plus"></i> Add Room Type</button>
    </div>
</div>

<!-- Form Section -->
<div id="roomForm" class="card" style="display: <?php echo $editing_room ? 'block' : 'none'; ?>; margin-bottom: 32px;">
    <div class="card-header">
        <h2 id="formTitle"><?php echo $editing_room ? 'Edit Room Type' : 'Add New Room Type'; ?></h2>
        <button onclick="toggleForm()" class="btn btn-sm btn-danger"><i class="fas fa-times"></i></button>
    </div>
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="action" id="formAction" value="<?php echo $editing_room ? 'edit' : 'add'; ?>">
            <input type="hidden" name="id" id="roomId" value="<?php echo $editing_room['id'] ?? ''; ?>">
            <input type="hidden" name="hotel_id" value="<?php echo $selected_hotel_id; ?>">
            
            <div style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 24px;">
                <div class="form-group">
                    <label>Room Name (e.g., Deluxe Suite)</label>
                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($editing_room['name'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label>Price / Night</label>
                    <input type="number" step="0.01" name="price" class="form-control" value="<?php echo $editing_room['price_per_night'] ?? ''; ?>" required>
                </div>
                <div class="form-group">
                    <label>Capacity</label>
                    <input type="number" name="capacity" class="form-control" value="<?php echo $editing_room['capacity'] ?? ''; ?>" required>
                </div>
                <div class="form-group">
                    <label>Quantity</label>
                    <input type="number" name="quantity" class="form-control" value="<?php echo $editing_room['quantity'] ?? ''; ?>" required>
                </div>
            </div>
            
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="3" required><?php echo htmlspecialchars($editing_room['description'] ?? ''); ?></textarea>
            </div>
            
            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <button type="button" onclick="toggleForm()" class="btn">Cancel</button>
                <button type="submit" class="btn btn-primary"><?php echo $editing_room ? 'Update Room' : 'Save Room'; ?></button>
            </div>
        </form>
    </div>
</div>

<!-- Rooms List -->
<div class="card">
    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th>Room Type</th>
                    <th>Price</th>
                    <th>Capacity</th>
                    <th>Quantity</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rooms)): ?>
                    <tr><td colspan="5" style="text-align: center; color: var(--text-muted);">No room types found for this hotel.</td></tr>
                <?php else: ?>
                    <?php foreach($rooms as $room): ?>
                        <tr>
                            <td style="font-weight: 600;"><?php echo htmlspecialchars($room['name']); ?></td>
                            <td><?php echo format_price($room['price_per_night']); ?></td>
                            <td><i class="fas fa-users"></i> <?php echo $room['capacity']; ?> Guests</td>
                            <td><?php echo $room['quantity']; ?> Rooms</td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <a href="?hotel_id=<?php echo $selected_hotel_id; ?>&edit=<?php echo $room['id']; ?>" class="btn btn-sm" title="Edit"><i class="fas fa-edit"></i></a>
                                    <a href="?hotel_id=<?php echo $selected_hotel_id; ?>&delete=<?php echo $room['id']; ?>" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this room type?')"><i class="fas fa-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function toggleForm(action = null) {
        const form = document.getElementById('roomForm');
        if (!action && form.style.display === 'none') return;
        
        if (action === 'add') {
            document.getElementById('formTitle').innerText = 'Add New Room Type';
            document.getElementById('formAction').value = 'add';
            document.getElementById('roomId').value = '';
            document.querySelector('#roomForm form').reset();
            form.style.display = 'block';
        } else if (!action) {
            form.style.display = 'none';
        } else {
            form.style.display = 'block';
        }
    }
</script>

<?php require_once 'includes/footer.php'; ?>
