<!-- Add Sub Category Modal -->
<div class="modal fade" id="addSubCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Add Sub Category</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="addSubCategoryForm">
                @csrf

                <div class="modal-body">

                    <!-- Category Dropdown -->
                    <div class="mb-3">
                        <label class="form-label">Select Category</label>

                        <select name="category_id" id="category_id" class="form-control" required>
                            <option value="">-- Select Category --</option>

                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>

                        <div class="invalid-feedback">
                            Please select a category.
                        </div>
                    </div>

                    <!-- Sub Category Name -->
                    <div class="mb-3">
                        <label class="form-label">Sub Category Name</label>

                        <input type="text"
                            id="name"
                            class="form-control"
                            placeholder="Enter sub category name"
                            required
                        >
                    </div>

                    <!-- Active Status -->
                    <div class="form-check form-switch">
                        <input class="form-check-input"
                            type="checkbox"
                            id="is_active"
                            checked
                        >
                        <label class="form-check-label">Active</label>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="button" class="btn btn-primary" id="saveBtn">
                        Save Sub Category
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>
