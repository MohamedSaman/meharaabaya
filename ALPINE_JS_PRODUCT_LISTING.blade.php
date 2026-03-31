<!-- 
    CLIENT-SIDE PRODUCT LISTING WITH ALPINE.JS
    Replace the product table section in productes.blade.php with this code
    
    Features:
    - Load products once via AJAX
    - Client-side search (instant, no server requests)
    - Client-side pagination
    - Client-side sorting
    - LocalStorage caching
    - Refresh button to reload data
-->

<div x-data="productManager()" x-init="init()" class="container-fluid p-3">
    <div id="products" class="tab-content active">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h3 class="fw-bold text-dark mb-2">
                    <i class="bi bi-box-seam text-success me-2"></i> Product Inventory Management
                </h3>
                <p class="text-muted mb-0">Manage your product catalog and inventory levels efficiently</p>
            </div>
        </div>

        <!-- Search Bar & Actions -->
        <div class="inventory-header w-100 mb-3">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center w-100 gap-3">
                
                <!-- Search Input (Client-Side) -->
                <div class="search-bar flex-grow-1">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input
                            type="text"
                            class="form-control border-start-0"
                            x-model="searchQuery"
                            @input="filterProducts()"
                            placeholder="Search Products... (instant search, no server request)">
                        <button class="btn btn-outline-secondary" x-show="searchQuery" @click="searchQuery = ''; filterProducts()" type="button">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <small class="text-muted" x-show="loading">
                        <i class="spinner-border spinner-border-sm me-1"></i> Loading products...
                    </small>
                    <small class="text-success" x-show="!loading && products.length > 0">
                        <i class="bi bi-check-circle me-1"></i> 
                        Showing <span x-text="filteredProducts.length"></span> of <span x-text="products.length"></span> products (cached)
                    </small>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex gap-2 justify-content-md-end">
                    <button class="btn btn-outline-primary" @click="refreshProducts()" :disabled="loading">
                        <i class="bi bi-arrow-clockwise" :class="{'fa-spin': loading}"></i>
                        <span class="d-none d-sm-inline">Refresh</span>
                    </button>
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importProductsModal">
                        <i class="bi bi-file-earmark-excel"></i>
                        <span class="d-none d-sm-inline">Import Excel</span>
                    </button>
                    <button class="btn btn-primary add-product-btn" wire:click="openCreateModal">
                        <i class="bi bi-plus-lg"></i>
                        <span class="d-none d-sm-inline">Add Product</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Products Table -->
        <div class="row g-4">
            <div class="col-12">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold text-dark mb-1">
                                <i class="bi bi-list-ul text-primary me-2"></i> Products List
                            </h5>
                            <small class="text-muted">
                                Page <span x-text="currentPage"></span> of <span x-text="totalPages"></span>
                            </small>
                        </div>
                        <div class="d-flex gap-2 align-items-center">
                            <label class="mb-0 me-2">Per Page:</label>
                            <select class="form-select form-select-sm" x-model="perPage" @change="filterProducts()" style="width: auto;">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <!-- Loading State -->
                        <template x-if="loading">
                            <div class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="text-muted mt-3">Loading products from cache...</p>
                            </div>
                        </template>

                        <!-- Empty State -->
                        <template x-if="!loading && paginatedProducts.length === 0">
                            <div class="text-center py-5">
                                <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
                                <p class="text-muted mt-3">No products found</p>
                                <template x-if="searchQuery">
                                    <button class="btn btn-sm btn-primary" @click="searchQuery = ''; filterProducts()">
                                        Clear Search
                                    </button>
                                </template>
                            </div>
                        </template>

                        <!-- Products Table -->
                        <template x-if="!loading && paginatedProducts.length > 0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th @click="sortBy('code')" style="cursor: pointer;">
                                                Code 
                                                <i class="bi" :class="sortColumn === 'code' ? (sortDirection === 'asc' ? 'bi-arrow-up' : 'bi-arrow-down') : 'bi-arrow-down-up'"></i>
                                            </th>
                                            <th @click="sortBy('product_name')" style="cursor: pointer;">
                                                Name 
                                                <i class="bi" :class="sortColumn === 'product_name' ? (sortDirection === 'asc' ? 'bi-arrow-up' : 'bi-arrow-down') : 'bi-arrow-down-up'"></i>
                                            </th>
                                            <th @click="sortBy('brand')" style="cursor: pointer;">
                                                Brand 
                                                <i class="bi" :class="sortColumn === 'brand' ? (sortDirection === 'asc' ? 'bi-arrow-up' : 'bi-arrow-down') : 'bi-arrow-down-up'"></i>
                                            </th>
                                            <th @click="sortBy('category')" style="cursor: pointer;">
                                                Category 
                                                <i class="bi" :class="sortColumn === 'category' ? (sortDirection === 'asc' ? 'bi-arrow-up' : 'bi-arrow-down') : 'bi-arrow-down-up'"></i>
                                            </th>
                                            <th @click="sortBy('selling_price')" style="cursor: pointer;">
                                                Price 
                                                <i class="bi" :class="sortColumn === 'selling_price' ? (sortDirection === 'asc' ? 'bi-arrow-up' : 'bi-arrow-down') : 'bi-arrow-down-up'"></i>
                                            </th>
                                            <th @click="sortBy('available_stock')" style="cursor: pointer;">
                                                Stock 
                                                <i class="bi" :class="sortColumn === 'available_stock' ? (sortDirection === 'asc' ? 'bi-arrow-up' : 'bi-arrow-down') : 'bi-arrow-down-up'"></i>
                                            </th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="product in paginatedProducts" :key="product.id">
                                            <tr>
                                                <td class="font-monospace" x-text="product.code"></td>
                                                <td>
                                                    <strong x-text="product.product_name"></strong>
                                                    <br><small class="text-muted" x-text="product.model" x-show="product.model"></small>
                                                </td>
                                                <td x-text="product.brand || 'N/A'"></td>
                                                <td x-text="product.category || 'N/A'"></td>
                                                <td>
                                                    <strong>Rs. <span x-text="parseFloat(product.selling_price).toFixed(2)"></span></strong>
                                                </td>
                                                <td>
                                                    <span 
                                                        class="badge"
                                                        :class="{
                                                            'bg-success': product.available_stock > 50,
                                                            'bg-warning text-dark': product.available_stock > 0 && product.available_stock <= 50,
                                                            'bg-danger': product.available_stock <= 0
                                                        }"
                                                        x-text="product.available_stock">
                                                    </span>
                                                </td>
                                                <td>
                                                    <span 
                                                        class="badge"
                                                        :class="{
                                                            'bg-success': product.status === 'active',
                                                            'bg-danger': product.status === 'inactive',
                                                            'bg-secondary': product.status === 'discontinued'
                                                        }"
                                                        x-text="product.status">
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="action-btns">
                                                        <button class="action-btn view" @click="$wire.viewProductDetails(product.id)" title="View">
                                                            <i class="bi bi-eye"></i>
                                                        </button>
                                                        <button class="action-btn edit" @click="$wire.editProduct(product.id)" title="Edit">
                                                            <i class="bi bi-pencil"></i>
                                                        </button>
                                                        <button class="action-btn delete" @click="$wire.confirmDeleteProduct(product.id)" title="Delete">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </template>
                    </div>

                    <!-- Pagination -->
                    <div class="card-footer" x-show="!loading && filteredProducts.length > 0">
                        <nav>
                            <ul class="pagination justify-content-center mb-0">
                                <li class="page-item" :class="{ 'disabled': currentPage === 1 }">
                                    <button class="page-link" @click="goToPage(currentPage - 1)">Previous</button>
                                </li>
                                
                                <template x-for="page in pageNumbers" :key="page">
                                    <li class="page-item" :class="{ 'active': page === currentPage }">
                                        <button class="page-link" @click="goToPage(page)" x-text="page"></button>
                                    </li>
                                </template>

                                <li class="page-item" :class="{ 'disabled': currentPage === totalPages }">
                                    <button class="page-link" @click="goToPage(currentPage + 1)">Next</button>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function productManager() {
    return {
        // Data
        products: [],
        filteredProducts: [],
        paginatedProducts: [],
        
        // Search & Filter
        searchQuery: '',
        
        // Sorting
        sortColumn: 'code',
        sortDirection: 'asc',
        
        // Pagination
        currentPage: 1,
        perPage: 10,
        totalPages: 1,
        
        // State
        loading: false,
        lastFetched: null,
        
        // Initialize
        async init() {
            // Try to load from LocalStorage first
            this.loadFromStorage();
            
            // If no cached data or cache is old (>5 min), fetch from server
            if (this.products.length === 0 || this.isCacheExpired()) {
                await this.fetchProducts();
            } else {
                this.filterProducts();
            }
            
            // Listen for refresh events from Livewire
            window.addEventListener('refreshPage', () => {
                this.refreshProducts();
            });
        },
        
        // Fetch products from server
        async fetchProducts() {
            this.loading = true;
            try {
                const response = await fetch('/api/products/all');
                const result = await response.json();
                
                if (result.success) {
                    this.products = result.data;
                    this.lastFetched = new Date().toISOString();
                    this.saveToStorage();
                    this.filterProducts();
                }
            } catch (error) {
                console.error('Error fetching products:', error);
                alert('Failed to load products. Please refresh the page.');
            } finally {
                this.loading = false;
            }
        },
        
        // Refresh products (force reload)
        async refreshProducts() {
            await this.fetchProducts();
        },
        
        // Filter products based on search
        filterProducts() {
            let filtered = this.products;
            
            // Apply search filter
            if (this.searchQuery) {
                const query = this.searchQuery.toLowerCase();
                filtered = filtered.filter(product => 
                    product.code.toLowerCase().includes(query) ||
                    product.product_name.toLowerCase().includes(query) ||
                    (product.brand && product.brand.toLowerCase().includes(query)) ||
                    (product.category && product.category.toLowerCase().includes(query)) ||
                    (product.model && product.model.toLowerCase().includes(query)) ||
                    product.status.toLowerCase().includes(query)
                );
            }
            
            this.filteredProducts = filtered;
            this.currentPage = 1; // Reset to first page
            this.paginate();
        },
        
        // Sort products
        sortBy(column) {
            if (this.sortColumn === column) {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortColumn = column;
                this.sortDirection = 'asc';
            }
            
            this.filteredProducts.sort((a, b) => {
                let aVal = a[column];
                let bVal = b[column];
                
                // Handle null/undefined
                if (aVal === null || aVal === undefined) return 1;
                if (bVal === null || bVal === undefined) return -1;
                
                // Numeric comparison
                if (typeof aVal === 'number' && typeof bVal === 'number') {
                    return this.sortDirection === 'asc' ? aVal - bVal : bVal - aVal;
                }
                
                // String comparison
                aVal = aVal.toString().toLowerCase();
                bVal = bVal.toString().toLowerCase();
                
                if (this.sortDirection === 'asc') {
                    return aVal < bVal ? -1 : aVal > bVal ? 1 : 0;
                } else {
                    return aVal > bVal ? -1 : aVal < bVal ? 1 : 0;
                }
            });
            
            this.paginate();
        },
        
        // Paginate filtered products
        paginate() {
            this.totalPages = Math.ceil(this.filteredProducts.length / this.perPage);
            const start = (this.currentPage - 1) * this.perPage;
            const end = start + parseInt(this.perPage);
            this.paginatedProducts = this.filteredProducts.slice(start, end);
        },
        
        // Go to specific page
        goToPage(page) {
            if (page < 1 || page > this.totalPages) return;
            this.currentPage = page;
            this.paginate();
        },
        
        // Generate page numbers for pagination
        get pageNumbers() {
            const pages = [];
            const maxPages = 5;
            let start = Math.max(1, this.currentPage - Math.floor(maxPages / 2));
            let end = Math.min(this.totalPages, start + maxPages - 1);
            
            if (end - start < maxPages - 1) {
                start = Math.max(1, end - maxPages + 1);
            }
            
            for (let i = start; i <= end; i++) {
                pages.push(i);
            }
            
            return pages;
        },
        
        // Save to LocalStorage
        saveToStorage() {
            const data = {
                products: this.products,
                lastFetched: this.lastFetched
            };
            localStorage.setItem('products_cache', JSON.stringify(data));
        },
        
        // Load from LocalStorage
        loadFromStorage() {
            const cached = localStorage.getItem('products_cache');
            if (cached) {
                try {
                    const data = JSON.parse(cached);
                    this.products = data.products || [];
                    this.lastFetched = data.lastFetched;
                } catch (error) {
                    console.error('Error loading from storage:', error);
                    localStorage.removeItem('products_cache');
                }
            }
        },
        
        // Check if cache is expired (>5 minutes)
        isCacheExpired() {
            if (!this.lastFetched) return true;
            const now = new Date();
            const fetched = new Date(this.lastFetched);
            const diff = (now - fetched) / 1000 / 60; // minutes
            return diff > 5;
        }
    }
}
</script>
