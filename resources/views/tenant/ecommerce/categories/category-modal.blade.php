<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="#" id="addCategoryForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-4">
                        <label for="categoryName" class="form-label">Category Name</label>
                        <input
                            name="name"
                            type="text"
                            id="categoryName"
                            class="form-control"
                            placeholder="Enter category name"
                            required
                        >
                        <div class="invalid-feedback">
                            Category name is required.
                        </div>
                    </div>

                    <div class="form-check form-switch mb-2">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            role="switch"
                            id="categoryIsActive"
                            name="is_active"
                            value="1"
                            checked
                        >
                        <label class="form-check-label" for="categoryIsActive">Status (Active)</label>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                   <button class="btn btn-primary" type="button" id="saveCategoryButton">
                     Save Category
                </button>
                </div>
            </form>
        </div>
    </div>
</div>
