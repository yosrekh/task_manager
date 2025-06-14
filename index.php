<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Get user info
$username = $_SESSION['user'];
$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}
$user_id = $user['id'];

function getTasks($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT * FROM tasks WHERE user_id = ? ORDER BY created_at ASC");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function getTaskById($pdo, $user_id, $task_id) {
    $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->execute([$task_id, $user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

$current_filter = $_GET['filter'] ?? 'all';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        $data = json_decode(file_get_contents('php://input'), true);
        if ($data['action'] === 'reorder') {
            $new_order_ids = $data['new_order'];
            foreach ($new_order_ids as $order => $task_id) {
                $stmt = $pdo->prepare("UPDATE tasks SET created_at = created_at, updated_at = NOW() WHERE id = ? AND user_id = ?");
                $stmt->execute([$task_id, $user_id]);
            }
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'message' => 'Order saved.']);
            exit;
        }
    }
    $action = $_POST['action'] ?? '';
    switch ($action) {
        case 'add':
            $task_text = trim($_POST['task_text'] ?? '');
            if (!empty($task_text)) {
                $stmt = $pdo->prepare("INSERT INTO tasks (user_id, title, status) VALUES (?, ?, 'pending')");
                $stmt->execute([$user_id, $task_text]);
            }
            break;
        case 'edit':
            $task_id = (int)($_POST['task_index'] ?? -1);
            $new_text = trim($_POST['task_text'] ?? '');
            if ($task_id > 0 && !empty($new_text)) {
                $stmt = $pdo->prepare("UPDATE tasks SET title = ? WHERE id = ? AND user_id = ?");
                $stmt->execute([$new_text, $task_id, $user_id]);
            }
            break;
        case 'toggle_status':
            $task_id = (int)($_POST['task_index'] ?? -1);
            $task = getTaskById($pdo, $user_id, $task_id);
            if ($task) {
                $new_status = ($task['status'] === 'completed') ? 'pending' : 'completed';
                $stmt = $pdo->prepare("UPDATE tasks SET status = ? WHERE id = ? AND user_id = ?");
                $stmt->execute([$new_status, $task_id, $user_id]);
            }
            break;
        case 'delete':
            $task_id = (int)($_POST['task_index'] ?? -1);
            if ($task_id > 0) {
                $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
                $stmt->execute([$task_id, $user_id]);
            }
            break;
    }
    header('Location: ' . strtok($_SERVER['PHP_SELF'], '?') . '?filter=' . $current_filter);
    exit;
}

$editing_index = isset($_GET['edit']) ? (int)$_GET['edit'] : -1;
$all_tasks = getTasks($pdo, $user_id);
$filtered_tasks = array_filter($all_tasks, function($task) use ($current_filter) {
    if ($current_filter === 'done') return $task['status'] === 'completed';
    if ($current_filter === 'not_done') return $task['status'] === 'pending';
    return true;
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Daily Tasks</title>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="main-header">
        <div class="header-content">
            <div class="header-left">
                <div class="header-logo">TM</div>
                <span class="header-title">Task Manager</span>
            </div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="user-greeting">Welcome, <strong><?= htmlspecialchars($username) ?></strong></span>
                <form action="logout.php" method="post" style="display:inline;">
                    <button type="submit" class="logout-btn">Logout</button>
                </form>
            </div>
        </div>
    </header>
    <div class="container">
        <h1>My Daily Tasks</h1>
        <form class="task-form" action="?filter=<?= $current_filter ?>" method="post">
            <input type="hidden" name="action" value="add">
            <input type="text" name="task_text" placeholder="Enter a new task..." required autocomplete="off">
            <button type="submit">Add Task</button>
        </form>
        <div class="task-filters">
            <a href="?filter=all" class="<?= $current_filter === 'all' ? 'filter-active' : '' ?>">All</a>
            <a href="?filter=not_done" class="<?= $current_filter === 'not_done' ? 'filter-active' : '' ?>">Not Done</a>
            <a href="?filter=done" class="<?= $current_filter === 'done' ? 'filter-active' : '' ?>">Done</a>
        </div>
        <ul class="task-list">
            <?php if (empty($filtered_tasks)): ?>
                <li class="no-tasks">
                    <?php
                        if ($current_filter === 'done') echo "No 'Done' tasks found.";
                        elseif ($current_filter === 'not_done') echo "No 'Not Done' tasks. Great job!";
                        else echo "You have no tasks yet. Add one above!";
                    ?>
                </li>
            <?php else: ?>
                <?php $animation_index = 0; ?>
                <?php foreach ($filtered_tasks as $task): ?>
                    <li class="task-item <?= $current_filter === 'all' ? 'draggable' : '' ?> <?= $task['status'] === 'completed' ? 'task-done' : '' ?>" 
                        data-index="<?= $task['id'] ?>" 
                        style="animation-delay: <?= $animation_index * 0.07 ?>s;">
                        <?php if ($editing_index === (int)$task['id']): ?>
                            <form class="task-form" action="?filter=<?= $current_filter ?>" method="post" style="flex-direction: row;">
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="task_index" value="<?= $task['id'] ?>">
                                <input type="text" name="task_text" value="<?= htmlspecialchars($task['title']) ?>" required autocomplete="off" autofocus>
                                <button type="submit">Save</button>
                                <a href="?filter=<?= $current_filter ?>" class="cancel-link">Cancel</a>
                            </form>
                        <?php else: ?>
                            <span class="task-text"><?= htmlspecialchars($task['title']) ?></span>
                            <div class="task-actions">
                                <form action="?filter=<?= $current_filter ?>" method="post">
                                    <input type="hidden" name="action" value="toggle_status"><input type="hidden" name="task_index" value="<?= $task['id'] ?>">
                                    <button type="submit" class="<?= $task['status'] === 'completed' ? 'btn-toggle-not-done' : 'btn-toggle-done' ?>" title="Toggle Status"><?= $task['status'] === 'completed' ? 'Not Done' : 'Done' ?></button>
                                </form>
                                <a href="?filter=<?= $current_filter ?>&edit=<?= $task['id'] ?>" class="btn-edit" title="Edit Task">Edit</a>
                                <form action="?filter=<?= $current_filter ?>" method="post" onsubmit="return confirm('Are you sure you want to delete this task?');">
                                    <input type="hidden" name="action" value="delete"><input type="hidden" name="task_index" value="<?= $task['id'] ?>">
                                    <button type="submit" class="btn-delete" title="Delete Task">×</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </li>
                    <?php $animation_index++; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
        <?php if ($current_filter !== 'all' && !empty($filtered_tasks)): ?>
            <div class="drag-message">
                Switch to the "All" view to reorder tasks.
            </div>
        <?php endif; ?>
    </div>
    <script>
        if ('<?= $current_filter ?>' === 'all') {
            const taskList = document.querySelector('.task-list');
            new Sortable(taskList, {
                animation: 150,
                handle: '.draggable',
                ghostClass: 'drag-ghost',
                chosenClass: 'drag-chosen',
                onEnd: function (evt) {
                    const items = evt.to.children;
                    let newOrder = [];
                    for (let i = 0; i < items.length; i++) {
                        newOrder.push(items[i].dataset.index);
                    }
                    fetch('<?= $_SERVER['PHP_SELF'] ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            action: 'reorder',
                            new_order: newOrder
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Success:', data.message);
                    })
                    .catch((error) => {
                        console.error('Error:', error);
                        alert('An error occurred while saving the new order.');
                    });
                }
            });
        }
    </script>
</body>
</html>