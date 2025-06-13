# Task Manager Web App

## What is this code?
This is a simple, modern Task Manager web app written in PHP. It lets you:

- Register and log in with your own account
- Add, edit, and delete tasks
- Mark tasks as done or not done
- Filter tasks (show all, only done, or only not done)
- Reorder tasks by dragging (when viewing "All")
- See your username and log out securely

---

## How does it work?

### 1. User Authentication
- The app now includes **Login** and **Signup** pages for user accounts.
- Each user has their own tasks, visible only when logged in.

### 2. Storing Tasks (v2)
- **Tasks are now stored in a MySQL database** (not in a JSON file).
- Each task is linked to the user who created it.
- The app uses PDO for secure database access.

### 3. Handling User Actions
- When you submit a form (add, edit, toggle, delete), the page reloads and PHP handles your request.
- If you drag and drop to reorder tasks, JavaScript sends an AJAX request to PHP, which saves the new order in the database.

### 4. Filtering
- You can filter tasks by clicking "All", "Not Done", or "Done".
- The filter is controlled by a URL parameter (e.g., `?filter=all`).

### 5. Displaying Tasks
- The tasks are shown in a list (`<ul>`), styled for clarity and usability.
- If you’re editing a task, it shows a form to change the text.
- Each task has buttons to mark as done/not done, edit, or delete.

### 6. Drag-and-Drop Reordering
- When viewing "All", you can drag tasks to reorder them.
- This uses a JavaScript library called SortableJS.
- When you drop a task, JavaScript sends the new order to PHP, which saves it in the database.

---

## What are the main parts?
- **PHP at the top:** Handles authentication, loading, saving, and updating tasks in the database.
- **HTML in the middle:** Shows the page, forms, and task list.
- **JavaScript at the bottom:** Handles drag-and-drop reordering.
- **Login and Signup pages:** Modern, animated, and responsive forms for user authentication.

---

## How does a typical action work?
- **Sign up:** Create a new account on the signup page. Your data is stored securely in the database.
- **Log in:** Enter your credentials on the login page to access your tasks.
- **Add a task:** Fill the form and submit. PHP adds it to your list in the database.
- **Edit a task:** Click "Edit", change the text, and save. PHP updates it in the database.
- **Mark as done/not done:** Click the button. PHP toggles the status in the database.
- **Delete:** Click the × button. PHP removes it from the database.
- **Reorder:** Drag tasks (in "All" view). JavaScript sends new order to PHP, which saves it in the database.
- **Logout:** Click the logout button in the header to securely end your session.

---

## Summary
This code is a simple, database-backed to-do list app with user authentication, CRUD (Create, Read, Update, Delete) features, and drag-and-drop sorting. It uses PHP for the backend, MySQL for storage, HTML/CSS for the frontend, and JavaScript for drag-and-drop.

**v2 now uses a database for all data storage and includes login/signup pages for a more secure, multi-user experience.**