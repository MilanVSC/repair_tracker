<?php
include_once 'includes/config.php';
include_once 'includes/auth.php';
requireLogin();
?>

<?php include_once 'includes/header.php'; ?>
    <h2>Nadzorna plošča</h2>
    <p>Dobrodošli, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</p>

    <div style="margin-bottom: 20px;">
        <a href="all_repairs.php" class="button">Ogled vseh popravil</a>
        <a href="add_repair.php" class="button">Novo popravilo</a>
        <a href="profile.php" class="button">Uredi profil</a>
    </div>

    <h3>Moja zadnja popravila</h3>
    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Naprava</th>
            <th>Naslov</th>
            <th>Status</th>
            <th>Prioriteta</th>
            <th>Datum</th>
            <th>Akcije</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $sql = "SELECT r.id, d.name as device_name, r.title, r.status, r.priority, r.reported_date 
                FROM repairs r
                JOIN devices d ON r.device_id = d.id
                WHERE r.reported_by = ?
                ORDER BY r.reported_date DESC
                LIMIT 5";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $_SESSION["user_id"]);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row["id"] . "</td>";
                    echo "<td>" . htmlspecialchars($row["device_name"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["title"]) . "</td>";
                    echo "<td><span class='status-" . $row["status"] . "'>" . $row["status"] . "</span></td>";
                    echo "<td><span class='priority-" . $row["priority"] . "'>" . $row["priority"] . "</span></td>";
                    echo "<td>" . date('d.m.Y H:i', strtotime($row["reported_date"])) . "</td>";
                    echo "<td><a href='view_repair.php?id=" . $row["id"] . "'>Ogled</a></td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='7'>Ni najdenih popravil</td></tr>";
            }
            $stmt->close();
        }
        ?>
        </tbody>
    </table>

<?php if (isAdmin() || isTechnician()): ?>
    <h3>Dodeljena popravila</h3>
    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Naprava</th>
            <th>Naslov</th>
            <th>Status</th>
            <th>Prioriteta</th>
            <th>Datum</th>
            <th>Akcije</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $sql = "SELECT r.id, d.name as device_name, r.title, r.status, r.priority, r.reported_date 
                FROM repairs r
                JOIN devices d ON r.device_id = d.id
                WHERE r.assigned_to = ?
                ORDER BY r.reported_date DESC
                LIMIT 5";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $_SESSION["user_id"]);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row["id"] . "</td>";
                    echo "<td>" . htmlspecialchars($row["device_name"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["title"]) . "</td>";
                    echo "<td><span class='status-" . $row["status"] . "'>" . $row["status"] . "</span></td>";
                    echo "<td><span class='priority-" . $row["priority"] . "'>" . $row["priority"] . "</span></td>";
                    echo "<td>" . date('d.m.Y H:i', strtotime($row["reported_date"])) . "</td>";
                    echo "<td><a href='view_repair.php?id=" . $row["id"] . "'>Ogled</a></td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='7'>Ni dodeljenih popravil</td></tr>";
            }
            $stmt->close();
        }
        ?>
        </tbody>
    </table>

    <h3>Popravila brez dodelitve</h3>
    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Naprava</th>
            <th>Naslov</th>
            <th>Status</th>
            <th>Prioriteta</th>
            <th>Datum</th>
            <th>Akcije</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $sql = "SELECT r.id, d.name as device_name, r.title, r.status, r.priority, r.reported_date 
                FROM repairs r
                JOIN devices d ON r.device_id = d.id
                WHERE r.assigned_to IS NULL 
                AND r.status NOT IN ('completed', 'cancelled')
                ORDER BY r.priority DESC, r.reported_date DESC
                LIMIT 5";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row["id"] . "</td>";
                    echo "<td>" . htmlspecialchars($row["device_name"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["title"]) . "</td>";
                    echo "<td><span class='status-" . $row["status"] . "'>" . $row["status"] . "</span></td>";
                    echo "<td><span class='priority-" . $row["priority"] . "'>" . $row["priority"] . "</span></td>";
                    echo "<td>" . date('d.m.Y H:i', strtotime($row["reported_date"])) . "</td>";
                    echo "<td>";
                    echo "<a href='view_repair.php?id=" . $row["id"] . "'>Ogled</a>";
                    if (isTechnician()): ?>
                        <form action="take_repair.php" method="post" style="display: inline;">
                            <input type="hidden" name="repair_id" value="<?php echo $row["id"]; ?>">
                            <button type="submit" style="background: none; border: none; color: #28a745; cursor: pointer; text-decoration: underline;">Prevzemi</button>
                        </form>
                    <?php endif;
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='7'>Ni popravil brez dodelitve</td></tr>";
            }
            $stmt->close();
        }
        ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include_once 'includes/footer.php'; ?>