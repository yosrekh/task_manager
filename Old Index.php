<?php
// Define the path to the JSON file where tasks will be stored
define('TASK_FILE', 'tasks.json');

// --- Helper Functions ---
function getTasks() {
    if (!file_exists(TASK_FILE)) {
        file_put_contents(TASK_FILE, '[]');
    }
    return json_decode(file_get_contents(TASK_FILE), true);
}

function saveTasks($tasks) {
    $tasks = array_values($tasks); 
    file_put_contents(TASK_FILE, json_encode($tasks, JSON_PRETTY_PRINT));
}

// Get the current filter from the URL
$current_filter = $_GET['filter'] ?? 'all';

// --- Logic to Handle Form/AJAX Submissions ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tasks = getTasks();

    // Check for AJAX request (reordering)
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        // NEW: Handle reordering tasks
        if ($data['action'] === 'reorder') {
            $new_order_indices = $data['new_order'];
            $reordered_tasks = [];
            foreach ($new_order_indices as $index) {
                if (isset($tasks[$index])) {
                    $reordered_tasks[] = $tasks[$index];
                }
            }
            saveTasks($reordered_tasks);
            // Send a success response back to JavaScript
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'message' => 'Order saved.']);
            exit;
        }
    }

    // Handle standard form submissions
    $action = $_POST['action'] ?? '';
    switch ($action) {
        case 'add':
            $task_text = trim($_POST['task_text'] ?? '');
            if (!empty($task_text)) {
                $tasks[] = ['text' => $task_text, 'status' => 'not_done'];
            }
            break;
        case 'edit':
            $task_index = (int)($_POST['task_index'] ?? -1);
            $new_text = trim($_POST['task_text'] ?? '');
            if (isset($tasks[$task_index]) && !empty($new_text)) {
                $tasks[$task_index]['text'] = $new_text;
            }
            break;
        case 'toggle_status':
            $task_index = (int)($_POST['task_index'] ?? -1);
            if (isset($tasks[$task_index])) {
                $tasks[$task_index]['status'] = ($tasks[$task_index]['status'] === 'done') ? 'not_done' : 'done';
            }
            break;
        case 'delete':
            $task_index = (int)($_POST['task_index'] ?? -1);
            if (isset($tasks[$task_index])) {
                unset($tasks[$task_index]);
            }
            break;
    }
    
    saveTasks($tasks);
    header('Location: ' . strtok($_SERVER['PHP_SELF'], '?') . '?filter=' . $current_filter);
    exit;
}

// --- Logic to Prepare Data for Display (GET requests) ---
$editing_index = isset($_GET['edit']) ? (int)$_GET['edit'] : -1;
$all_tasks = getTasks();
$filtered_tasks = array_filter($all_tasks, function($task) use ($current_filter) {
    if ($current_filter === 'done') return $task['status'] === 'done';
    if ($current_filter === 'not_done') return $task['status'] === 'not_done';
    return true; // 'all'
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Daily Tasks</title>
    <!-- NEW: Include SortableJS library from a CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="container">
        <h1>My Daily Tasks</h1>

        <!-- Form for adding a new task -->
        <form class="task-form" action="?filter=<?= $current_filter ?>" method="post">
            <input type="hidden" name="action" value="add">
            <input type="text" name="task_text" placeholder="Enter a new task..." required autocomplete="off">
            <button type="submit">Add Task</button>
        </form>

        <!-- Filter controls -->
        <div class="task-filters">
            <a href="?filter=all" class="<?= $current_filter === 'all' ? 'filter-active' : '' ?>">All</a>
            <a href="?filter=not_done" class="<?= $current_filter === 'not_done' ? 'filter-active' : '' ?>">Not Done</a>
            <a href="?filter=done" class="<?= $current_filter === 'done' ? 'filter-active' : '' ?>">Done</a>
        </div>

        <!-- List of tasks -->
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
                <?php foreach ($filtered_tasks as $index => $task): ?>
                    <!-- NEW: Add data-index for JS and a draggable class if filter is 'all' -->
                    <li class="task-item <?= $current_filter === 'all' ? 'draggable' : '' ?> <?= $task['status'] === 'done' ? 'task-done' : '' ?>" 
                        data-index="<?= $index ?>" 
                        style="animation-delay: <?= $animation_index * 0.07 ?>s;">
                        
                        <?php if ($editing_index === $index): ?>
                            <!-- EDITING VIEW -->
                            <form class="task-form" action="?filter=<?= $current_filter ?>" method="post" style="flex-direction: row;">
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="task_index" value="<?= $index ?>">
                                <input type="text" name="task_text" value="<?= htmlspecialchars($task['text']) ?>" required autocomplete="off" autofocus>
                                <button type="submit">Save</button>
                                <a href="?filter=<?= $current_filter ?>" class="cancel-link">Cancel</a>
                            </form>
                        <?php else: ?>
                            <!-- NORMAL DISPLAY VIEW -->
                            <span class="task-text"><?= htmlspecialchars($task['text']) ?></span>
                            <div class="task-actions">
                                <!-- Action forms... -->
                                <form action="?filter=<?= $current_filter ?>" method="post">
                                    <input type="hidden" name="action" value="toggle_status"><input type="hidden" name="task_index" value="<?= $index ?>">
                                    <button type="submit" class="<?= $task['status'] === 'done' ? 'btn-toggle-not-done' : 'btn-toggle-done' ?>" title="Toggle Status"><?= $task['status'] === 'done' ? 'Not Done' : 'Done' ?></button>
                                </form>
                                <a href="?filter=<?= $current_filter ?>&edit=<?= $index ?>" class="btn-edit" title="Edit Task">Edit</a>
                                <form action="?filter=<?= $current_filter ?>" method="post" onsubmit="return confirm('Are you sure you want to delete this task?');">
                                    <input type="hidden" name="action" value="delete"><input type="hidden" name="task_index" value="<?= $index ?>">
                                    <button type="submit" class="btn-delete" title="Delete Task">×</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </li>
                    <?php $animation_index++; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
        
        <!-- NEW: Message to show if filters are active -->
        <?php if ($current_filter !== 'all' && !empty($filtered_tasks)): ?>
            <div class="drag-message">
                Switch to the "All" view to reorder tasks.
            </div>
        <?php endif; ?>
    </div>

    <!-- NEW: JavaScript to handle drag-and-drop -->
    <script>
        // Only enable drag-and-drop if the filter is 'all'
        if ('<?= $current_filter ?>' === 'all') {
            const taskList = document.querySelector('.task-list');

            new Sortable(taskList, {
                animation: 150, // ms, animation speed moving items when sorting, `0` — without animation
                handle: '.draggable', // Restricts sort start click/touch to the specified element
                ghostClass: 'drag-ghost', // Class name for the drop placeholder
                chosenClass: 'drag-chosen', // Class name for the chosen item
                
                // Called when a drop event occurs
                onEnd: function (evt) {
                    const items = evt.to.children;
                    let newOrder = [];
                    for (let i = 0; i < items.length; i++) {
                        // Get the original index from the data-index attribute
                        newOrder.push(items[i].dataset.index);
                    }

                    // Send the new order to the server via AJAX
                    fetch('<?= $_SERVER['PHP_SELF'] ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest' // Identify this as an AJAX request
                        },
                        body: JSON.stringify({
                            action: 'reorder',
                            new_order: newOrder
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Success:', data.message);
                        // You could add a small "Saved!" notification here if desired
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