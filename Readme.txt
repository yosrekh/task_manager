What is this code?
------------------
This is a simple Task Manager web app written in PHP. It lets you:

- Add tasks
- Edit tasks
- Mark tasks as done or not done
- Delete tasks
- Filter tasks (show all, only done, or only not done)
- Reorder tasks by dragging (when viewing "All")

How does it work?
-----------------
1. Storing Tasks
   - Tasks are saved in a file called `tasks.json` (a simple text file in JSON format).
   - There are helper functions:
     - `getTasks()` — loads all tasks from the file.
     - `saveTasks($tasks)` — saves all tasks back to the file.

2. Handling User Actions
   - When you submit a form (add, edit, toggle, delete), the page reloads and PHP handles your request.
   - If you drag and drop to reorder tasks, JavaScript sends an AJAX request to PHP, which saves the new order.

3. Filtering
   - You can filter tasks by clicking "All", "Not Done", or "Done".
   - The filter is controlled by a URL parameter (e.g., `?filter=all`).

4. Displaying Tasks
   - The tasks are shown in a list (`<ul>`).
   - If you’re editing a task, it shows a form to change the text.
   - Each task has buttons to mark as done/not done, edit, or delete.

5. Drag-and-Drop Reordering
   - When viewing "All", you can drag tasks to reorder them.
   - This uses a JavaScript library called SortableJS.
   - When you drop a task, JavaScript sends the new order to PHP, which saves it.

What are the main parts?
------------------------
- **PHP at the top:** Handles loading, saving, and updating tasks.
- **HTML in the middle:** Shows the page, forms, and task list.
- **JavaScript at the bottom:** Handles drag-and-drop reordering.

How does a typical action work?
-------------------------------
- **Add a task:** Fill the form and submit. PHP adds it to the list and saves.
- **Edit a task:** Click "Edit", change the text, and save. PHP updates it.
- **Mark as done/not done:** Click the button. PHP toggles the status.
- **Delete:** Click the × button. PHP removes it.
- **Reorder:** Drag tasks (in "All" view). JavaScript sends new order to PHP.

Summary
-------
This code is a simple, file-based to-do list app with basic CRUD (Create, Read, Update, Delete) features and drag-and-drop sorting. It uses PHP for the backend, HTML/CSS for the frontend, and a bit of JavaScript for drag-and-drop.