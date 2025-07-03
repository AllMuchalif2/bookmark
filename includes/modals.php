<!-- Add Bookmark Modal -->
<div id="addBookmarkModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-gray-800 rounded-lg shadow-xl p-6 w-full max-w-md mx-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Add Bookmark</h3>
            <button onclick="toggleModal('addBookmarkModal')" class="text-gray-400 hover:text-gray-300">
                &times;
            </button>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="action" value="add_bookmark">
            <div class="mb-4">
                <label class="block text-gray-400 mb-2">Title</label>
                <input type="text" name="title" required class="w-full px-3 py-2 bg-dark-700 border border-dark-600 rounded focus:outline-none focus:border-blue-500 text-gray-900">
            </div>
            <div class="mb-4">
                <label class="block text-gray-400 mb-2">URL</label>
                <input type="url" name="url" required class="w-full px-3 py-2 bg-dark-700 border border-dark-600 rounded focus:outline-none focus:border-blue-500 text-gray-900">
            </div>
            <div class="mb-4">
                <label class="block text-gray-400 mb-2">Category</label>
                <select name="category_id" class="w-full px-3 py-2 bg-dark-700 border border-dark-600 rounded focus:outline-none focus:border-blue-500 text-gray-900">
                    <option value="">-- No Category --</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="toggleModal('addBookmarkModal')" class="px-4 py-2 border border-dark-600 rounded hover:bg-dark-700">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 rounded hover:bg-blue-700">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Bookmark Modal -->
<div id="editBookmarkModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-gray-800 rounded-lg shadow-xl p-6 w-full max-w-md mx-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Edit Bookmark</h3>
            <button onclick="toggleModal('editBookmarkModal')" class="text-gray-400 hover:text-gray-300">
                &times;
            </button>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="action" value="update_bookmark">
            <input type="hidden" id="editBookmarkId" name="id" value="">
            <div class="mb-4">
                <label class="block text-gray-400 mb-2">Title</label>
                <input type="text" id="editBookmarkTitle" name="title" required class="w-full px-3 py-2 bg-dark-700 border border-dark-600 rounded focus:outline-none focus:border-blue-500 text-gray-900">
            </div>
            <div class="mb-4">
                <label class="block text-gray-400 mb-2">URL</label>
                <input type="url" id="editBookmarkUrl" name="url" required class="w-full px-3 py-2 bg-dark-700 border border-dark-600 rounded focus:outline-none focus:border-blue-500 text-gray-900">
            </div>
            <div class="mb-4">
                <label class="block text-gray-400 mb-2">Category</label>
                <select id="editBookmarkCategory" name="category_id" class="w-full px-3 py-2 bg-dark-700 border border-dark-600 rounded focus:outline-none focus:border-blue-500 text-gray-900">
                    <option value="">-- No Category --</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="toggleModal('editBookmarkModal')" class="px-4 py-2 border border-dark-600 rounded hover:bg-dark-700">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 rounded hover:bg-blue-700">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Category Modal -->
<div id="addCategoryModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-gray-800 rounded-lg shadow-xl p-6 w-full max-w-md mx-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Add Category</h3>
            <button onclick="toggleModal('addCategoryModal')" class="text-gray-400 hover:text-gray-300">
                &times;
            </button>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="action" value="add_category">
            <div class="mb-4">
                <label class="block text-gray-400 mb-2">Name</label>
                <input type="text" name="name" required class="w-full px-3 py-2 bg-dark-700 border border-dark-600 rounded focus:outline-none focus:border-blue-500 text-gray-900">
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="toggleModal('addCategoryModal')" class="px-4 py-2 border border-dark-600 rounded hover:bg-dark-700">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-purple-600 rounded hover:bg-purple-700">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Category Modal -->
<div id="editCategoryModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-gray-800 rounded-lg shadow-xl p-6 w-full max-w-md mx-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Edit Category</h3>
            <button onclick="toggleModal('editCategoryModal')" class="text-gray-400 hover:text-gray-300">
                &times;
            </button>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="action" value="update_category">
            <input type="hidden" id="editCategoryId" name="id" value="">
            <div class="mb-4">
                <label class="block text-gray-400 mb-2">Name</label>
                <input type="text" id="editCategoryName" name="name" required class="w-full px-3 py-2 bg-dark-700 border border-dark-600 rounded focus:outline-none focus:border-blue-500 text-gray-900">
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="toggleModal('editCategoryModal')" class="px-4 py-2 border border-dark-600 rounded hover:bg-dark-700">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-purple-600 rounded hover:bg-purple-700">Save</button>
            </div>
        </form>
    </div>
</div>