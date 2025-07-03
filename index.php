<?php
require_once 'includes/functions.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        try {
            switch ($_POST['action']) {
                case 'add_bookmark':
                    addBookmark($db, $_POST['title'], $_POST['url'], $_POST['category_id']);
                    $_SESSION['message'] = 'Bookmark added successfully!';
                    break;
                case 'update_bookmark':
                    updateBookmark($db, $_POST['id'], $_POST['title'], $_POST['url'], $_POST['category_id']);
                    $_SESSION['message'] = 'Bookmark updated successfully!';
                    break;
                case 'delete_bookmark':
                    deleteBookmark($db, $_POST['id']);
                    $_SESSION['message'] = 'Bookmark deleted successfully!';
                    break;
                case 'add_category':
                    addCategory($db, $_POST['name']);
                    $_SESSION['message'] = 'Category added successfully!';
                    break;
                case 'update_category':
                    updateCategory($db, $_POST['id'], $_POST['name']);
                    $_SESSION['message'] = 'Category updated successfully!';
                    break;
                case 'delete_category':
                    deleteCategory($db, $_POST['id']);
                    $_SESSION['message'] = 'Category deleted successfully!';
                    break;
            }
            header("Location: " . BASE_URL);
            exit();
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Database error: ' . $e->getMessage();
        }
    }
}

// Get data
$categories = getCategories($db);
$bookmarks = getBookmarks($db);
$projects = getLaragonProjects();

// Group bookmarks by category
$bookmarksByCategory = [];
foreach ($categories as $category) {
    $bookmarksByCategory[$category['id']] = [
        'name' => $category['name'],
        'items' => []
    ];
}
$bookmarksByCategory['uncategorized'] = [
    'name' => 'Uncategorized',
    'items' => []
];

foreach ($bookmarks as $bookmark) {
    if ($bookmark['category_id']) {
        $bookmarksByCategory[$bookmark['category_id']]['items'][] = $bookmark;
    } else {
        $bookmarksByCategory['uncategorized']['items'][] = $bookmark;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browser Home - Bookmark Manager</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .category-row {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .category-container {
            flex: 1;
            min-width: 300px;
            background: #1e293b;
            border-radius: 0.5rem;
            padding: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .bookmarks-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 0.75rem;
            margin-top: 0.75rem;
        }
        .project-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 0.75rem;
        }
        .bookmark-card {
            background: #374151;
            border-radius: 0.5rem;
            padding: 0.75rem;
            transition: all 0.2s ease;
            position: relative;
            group: hover;
        }
        .bookmark-card:hover {
            background: #4b5563;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .bookmark-actions {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            display: flex;
            gap: 0.25rem;
            opacity: 0;
            transition: opacity 0.2s ease;
        }
        .bookmark-card:hover .bookmark-actions {
            opacity: 1;
        }
        .action-btn {
            background: rgba(0, 0, 0, 0.7);
            border: none;
            border-radius: 0.25rem;
            padding: 0.25rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .action-btn:hover {
            background: rgba(0, 0, 0, 0.9);
        }
        .btn-edit {
            color: #60a5fa;
        }
        .btn-delete {
            color: #f87171;
        }
        .modal {
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(4px);
        }
        .modal-content {
            background: #1f2937;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
    </style>
</head>
<body class="bg-gray-900 text-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <header class="mb-8 text-center">
            <h1 class="text-4xl font-bold mb-2" id="timeDisplay">00:00:00</h1>
            <div class="text-gray-400 mb-4">
                <p class="text-lg" id="dateDisplay">1 January 2024</p>
            </div>
            
            <!-- Google Search -->
            <div class="max-w-2xl mx-auto">
                <form action="https://www.google.com/search" method="GET" target="_blank" class="relative">
                    <input type="text" name="q" placeholder="Search Google..." 
                        class="w-full px-6 py-4 bg-gray-800 border border-gray-700 rounded-full text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent pr-16">
                    <button type="submit" class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-blue-600 hover:bg-blue-700 p-3 rounded-full transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>
                </form>
            </div>
        </header>

        <!-- Action Buttons -->
        <div class="mb-8 w-full">
            <div class="flex flex-col sm:flex-row gap-4 w-full">
                <button onclick="toggleModal('addBookmarkModal')" 
                    class="flex-1 bg-blue-600 hover:bg-blue-700 px-6 py-4 rounded-lg font-medium transition-colors text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Add Bookmark
                </button>
                <button onclick="toggleModal('addCategoryModal')" 
                    class="flex-1 bg-purple-600 hover:bg-purple-700 px-6 py-4 rounded-lg font-medium transition-colors text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Add Category
                </button>
            </div>
        </div>

        <!-- Categories -->
        <div class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">Bookmarks</h2>
            <div class="category-row">
                <?php 
                $categoryCount = 0;
                foreach ($bookmarksByCategory as $categoryId => $category): 
                    if ($categoryCount % 2 == 0 && $categoryCount > 0) {
                        echo '</div><div class="category-row">';
                    }
                    $categoryCount++;
                ?>
                <div class="category-container">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="text-lg font-semibold text-blue-400">
                            <?= htmlspecialchars($category['name']) ?>
                            <span class="text-sm text-gray-400 ml-2">(<?= count($category['items']) ?>)</span>
                        </h3>
                        <?php if ($categoryId !== 'uncategorized'): ?>
                            <div class="flex space-x-2">
                                <button onclick="editCategory(<?= is_numeric($categoryId) ? $categoryId : 'null' ?>, '<?= htmlspecialchars($category['name']) ?>')" 
                                    class="text-blue-400 hover:text-blue-300 p-1 rounded transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button onclick="deleteCategory(<?= is_numeric($categoryId) ? $categoryId : 'null' ?>)" 
                                    class="text-red-400 hover:text-red-300 p-1 rounded transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="bookmarks-grid">
                        <?php foreach (array_slice($category['items'], 0, 6) as $bookmark): ?>
                            <div class="bookmark-card group">
                                <div class="bookmark-actions">
                                    <button onclick="editBookmark(<?= $bookmark['id'] ?>, '<?= htmlspecialchars($bookmark['title'], ENT_QUOTES) ?>', '<?= htmlspecialchars($bookmark['url'], ENT_QUOTES) ?>', <?= $bookmark['category_id'] ?: 'null' ?>)" 
                                        class="action-btn btn-edit" title="Edit bookmark">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <button onclick="deleteBookmark(<?= $bookmark['id'] ?>)" 
                                        class="action-btn btn-delete" title="Delete bookmark">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                                <a href="<?= htmlspecialchars($bookmark['url']) ?>" target="_blank" class="block">
                                    <h4 class="font-medium text-sm truncate mb-1 pr-12"><?= htmlspecialchars($bookmark['title']) ?></h4>
                                    <p class="text-xs text-gray-400 truncate"><?= htmlspecialchars(parse_url($bookmark['url'], PHP_URL_HOST)) ?></p>
                                </a>
                            </div>
                        <?php endforeach; ?>
                        
                        <?php if (count($category['items']) > 6): ?>
                            <button onclick="showAllBookmarks('<?= $categoryId ?>')" 
                                class="bookmark-card text-center text-blue-400 hover:text-blue-300 border-2 border-dashed border-gray-600 hover:border-blue-400">
                                <div class="flex flex-col items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                    <span class="text-xs">+<?= count($category['items']) - 6 ?> more</span>
                                </div>
                            </button>
                        <?php elseif (count($category['items']) === 0): ?>
                            <div class="text-gray-500 text-sm italic p-4 text-center">
                                No bookmarks yet
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Laragon Projects -->
        <div class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">Laragon Projects</h2>
            <div class="project-grid">
                <?php foreach ($projects as $project): ?>
                    <a href="http://<?= htmlspecialchars($project['url']) ?>" target="_blank" 
                       class="block bg-slate-800 rounded-lg p-4 hover:bg-slate-700 transition-colors">
                        <div class="flex items-center mb-2">
                            <div class="bg-blue-600 p-2 rounded mr-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4l2 2h4a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <h3 class="font-medium"><?= htmlspecialchars($project['name']) ?></h3>
                        </div>
                        <p class="text-sm text-gray-400">Modified: <?= date('M d, Y', strtotime($project['modified'])) ?></p>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Modal for showing all bookmarks in category -->
    <div id="categoryBookmarksModal" class="modal fixed inset-0 flex items-center justify-center hidden z-50">
        <div class="modal-content rounded-lg shadow-xl p-6 w-full max-w-6xl mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-6">
                <h3 id="modalCategoryTitle" class="text-2xl font-semibold"></h3>
                <button onclick="toggleModal('categoryBookmarksModal')" 
                    class="text-gray-400 hover:text-gray-300 text-2xl leading-none">&times;</button>
            </div>
            <div id="modalBookmarksContainer" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4"></div>
        </div>
    </div>

    <?php include 'includes/modals.php'; ?>

    <script>
        // Update date and time
        function updateDateTime() {
            const now = new Date();
            
            // Format time as HH:MM:SS (24-hour format)
            const timeString = now.toLocaleTimeString('en-US', {
                hour12: false,
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            
            // Format date as DD Month YYYY
            const dateString = now.toLocaleDateString('en-US', {
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            });
            
            document.getElementById('timeDisplay').textContent = timeString;
            document.getElementById('dateDisplay').textContent = dateString;
        }

        // Update datetime every second
        updateDateTime();
        setInterval(updateDateTime, 1000);

        // Toggle modals
        function toggleModal(id) {
            document.getElementById(id).classList.toggle('hidden');
        }

        // Bookmark functions
        function editBookmark(id, title, url, categoryId) {
            document.getElementById('editBookmarkId').value = id;
            document.getElementById('editBookmarkTitle').value = title;
            document.getElementById('editBookmarkUrl').value = url;
            document.getElementById('editBookmarkCategory').value = categoryId || '';
            toggleModal('editBookmarkModal');
        }

        function deleteBookmark(id) {
            Swal.fire({
                title: 'Delete Bookmark?',
                text: "This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    submitForm('delete_bookmark', { id: id });
                }
            });
        }

        // Category functions
        function editCategory(id, name) {
            document.getElementById('editCategoryId').value = id;
            document.getElementById('editCategoryName').value = name;
            toggleModal('editCategoryModal');
        }

        function deleteCategory(id) {
            Swal.fire({
                title: 'Delete Category?',
                text: "Bookmarks in this category will become uncategorized.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    submitForm('delete_category', { id: id });
                }
            });
        }

        function showAllBookmarks(categoryId) {
            const categories = <?= json_encode($bookmarksByCategory) ?>;
            const category = categories[categoryId];
            
            document.getElementById('modalCategoryTitle').textContent = category.name;
            
            const container = document.getElementById('modalBookmarksContainer');
            container.innerHTML = '';
            
            category.items.forEach(bookmark => {
                const bookmarkEl = document.createElement('div');
                bookmarkEl.className = 'bookmark-card group';
                bookmarkEl.innerHTML = `
                    <div class="bookmark-actions">
                        <button onclick="editBookmark(${bookmark.id}, '${bookmark.title.replace(/'/g, "\\'")}', '${bookmark.url.replace(/'/g, "\\'")}', ${bookmark.category_id || null})" 
                            class="action-btn btn-edit" title="Edit bookmark">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </button>
                        <button onclick="deleteBookmark(${bookmark.id})" 
                            class="action-btn btn-delete" title="Delete bookmark">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                    <a href="${bookmark.url}" target="_blank" class="block">
                        <h4 class="font-medium mb-2 pr-12">${bookmark.title}</h4>
                        <p class="text-sm text-gray-400 break-all">${new URL(bookmark.url).hostname}</p>
                    </a>
                `;
                container.appendChild(bookmarkEl);
            });
            
            toggleModal('categoryBookmarksModal');
        }

        // Helper function to submit forms
        function submitForm(action, data) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '';
            
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = action;
            form.appendChild(actionInput);
            
            Object.keys(data).forEach(key => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = data[key];
                form.appendChild(input);
            });
            
            document.body.appendChild(form);
            form.submit();
        }

        // Show alerts from PHP session
        <?php if (isset($_SESSION['message'])): ?>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '<?= $_SESSION['message'] ?>',
                timer: 3000,
                showConfirmButton: false
            });
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: '<?= $_SESSION['error'] ?>'
            });
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
    </script>
</body>
</html>