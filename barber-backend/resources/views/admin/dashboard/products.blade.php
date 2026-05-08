@extends('admin.admindashboard')

@section('content')
<div class="main-tab-content active" id="produk-tab-content">
    <div class="content-card">
        <h1 class="dashboard-header">Kelola Daftar <span>Produk</span> & Layanan</h1>
        <p style="color: var(--text-grey); margin-bottom: 2rem;">Di sini Anda dapat melihat, menambah, mengedit, dan menghapus daftar layanan potong rambut, perawatan, dan produk ritel yang ditawarkan barbershop.</p>
        
        <!-- Form untuk tambah layanan -->
        <div class="form-section" style="margin-bottom: 2rem; padding: 1.5rem; background: var(--surface-light);">
            <h4 style="font-size: 1.2rem; color: var(--accent-gold); margin-bottom: 1rem;">Tambah Layanan Baru</h4>
            <form id="addServiceForm">
                <div class="form-row">
                    <div class="form-group">
                        <label>Nama Layanan</label>
                        <input type="text" id="serviceName" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Harga (Rp)</label>
                        <input type="number" id="servicePrice" name="price" required>
                    </div>
                    <div class="form-group">
                        <label>Durasi (menit)</label>
                        <input type="number" id="serviceDuration" name="duration" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea id="serviceDescription" name="description" style="height: 80px;"></textarea>
                </div>
                <div class="form-group">
                    <label>Foto Layanan (Upload dari Galeri/Lokal)</label>
                    <input type="file" id="serviceImage" name="image" accept="image/*">
                    <p style="font-size: 0.8rem; color: var(--text-grey); margin-top: 5px;">Pilih file gambar dari komputer Anda.</p>
                </div>
                <button type="submit" class="btn-primary" style="margin-top: 1rem;">Tambah Layanan</button>
            </form>
        </div>
        
        <h4 style="font-size: 1.5rem; border-top: var(--border-subtle); padding-top: 2rem; margin-top: 2rem; color: var(--accent-gold);">Daftar Layanan Cukur</h4>
        <div id="layananCukurList">
            @if(isset($services) && $services->count() > 0)
                @foreach($services as $service)
                    <div class="service-item" data-id="{{ $service->id }}">
                        <div class="service-info">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                @if($service->image_path || $service->image_url)
                                    <img src="{{ asset($service->image_path ?? $service->image_url) }}" style="width: 60px; height: 60px; border-radius: 8px; object-fit: cover;">
                                @endif
                                <div>
                                    <h4>{{ $service->name }}</h4>
                                    <p>{{ $service->description ?? '' }}</p>
                                    <span class="service-price">Rp {{ number_format($service->price, 0, ',', '.') }}</span>
                                    @if($service->duration)
                                        <p style="font-size: 0.85rem; margin-top: 4px;">Durasi: {{ $service->duration }} menit</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="service-actions">
                            <button class="btn-small btn-edit" onclick="editService({{ $service->id }})">Edit</button>
                            <button class="btn-small btn-delete" onclick="deleteService({{ $service->id }})">Hapus</button>
                        </div>
                    </div>
                @endforeach
            @else
                <p style="color: var(--text-grey); text-align: center; padding: 2rem;">Tidak ada layanan cukur</p>
            @endif
        </div>
        
        <!-- Form untuk tambah produk -->
        <div class="form-section" style="margin-bottom: 2rem; padding: 1.5rem; background: var(--surface-light); border-top: var(--border-subtle); margin-top: 2rem; padding-top: 2rem;">
            <h4 style="font-size: 1.2rem; color: var(--accent-gold); margin-bottom: 1rem;">Tambah Produk Baru</h4>
            <form id="addProductForm">
                <div class="form-row">
                    <div class="form-group">
                        <label>Nama Produk</label>
                        <input type="text" id="productName" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Harga (Rp)</label>
                        <input type="number" id="productPrice" name="price" required>
                    </div>
                    <div class="form-group">
                        <label>Stok</label>
                        <input type="number" id="productStock" name="stock_quantity" required>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select id="productStatus" name="status" required>
                            <option value="active">Aktif</option>
                            <option value="inactive">Tidak Aktif</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea id="productDescription" name="description" style="height: 80px;"></textarea>
                </div>
                <div class="form-group">
                    <label>Foto Produk (Upload dari Galeri/Lokal)</label>
                    <input type="file" id="productImage" name="image" accept="image/*">
                    <p style="font-size: 0.8rem; color: var(--text-grey); margin-top: 5px;">Pilih file gambar dari komputer Anda.</p>
                </div>
                <button type="submit" class="btn-primary" style="margin-top: 1rem;">Tambah Produk</button>
            </form>
        </div>
        
        <h4 style="font-size: 1.5rem; border-top: var(--border-subtle); padding-top: 2rem; margin-top: 2rem; color: var(--accent-gold);">Produk Ritel (Hanya Beli di Tempat)</h4>
        <div class="product-grid" id="produkRitelGrid">
            @if(isset($products) && $products->count() > 0)
                @foreach($products as $product)
                    <div class="product-card" data-id="{{ $product->id }}">
                        <img class="product-card-img" src="{{ asset($product->image_path ?? $product->image_url ?? $product->image ?? $product->img ?? 'assets/img/default-product.jpg') }}" alt="{{ $product->name ?? 'Product' }}">
                        <h3>{{ $product->name ?? 'N/A' }}</h3>
                        <span class="product-price">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                        <span class="stock-status {{ $product->status == 'active' || $product->stok ? 'ready' : 'out-of-stock' }}">{{ $product->status ?? $product->stok ?? 'Ready Stock' }}</span>
                        <div class="product-actions">
                            <button class="btn-small btn-edit" onclick="editProduct({{ $product->id }})">Edit</button>
                            <button class="btn-small btn-delete" onclick="deleteProduct({{ $product->id }})">Hapus</button>
                        </div>
                    </div>
                @endforeach
            @else
                <p style="color: var(--text-grey); text-align: center; padding: 2rem;">Tidak ada produk ritel</p>
            @endif
        </div>
    </div>
</div>

<!-- Modal untuk edit layanan -->
<div id="editServiceModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.4);">
    <div class="modal-content" style="background-color: var(--surface-card); margin: 5% auto; padding: 20px; border: none; border-radius: 8px; width: 60%; max-width: 600px;">
        <h3 style="color: var(--accent-gold);">Edit Layanan</h3>
        <form id="editServiceForm">
            <input type="hidden" id="editServiceId" name="id">
            <div class="form-group">
                <label>Nama Layanan</label>
                <input type="text" id="editServiceName" name="name" required>
            </div>
            <div class="form-group">
                <label>Harga (Rp)</label>
                <input type="number" id="editServicePrice" name="price" required>
            </div>
            <div class="form-group">
                <label>Durasi (menit)</label>
                <input type="number" id="editServiceDuration" name="duration" required>
            </div>
            <div class="form-group">
                <label>Deskripsi</label>
                <textarea id="editServiceDescription" name="description" style="height: 80px;"></textarea>
            </div>
            <div class="form-group">
                <label>Ganti Foto Layanan (Opsional)</label>
                <input type="file" id="editServiceImage" name="image" accept="image/*">
                <div id="currentServiceImage" style="margin-top: 10px;"></div>
            </div>
            <div style="margin-top: 1rem;">
                <button type="submit" class="btn-primary">Simpan Perubahan</button>
                <button type="button" class="btn-secondary" onclick="closeEditServiceModal()">Batal</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal untuk edit produk -->
<div id="editProductModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.4);">
    <div class="modal-content" style="background-color: var(--surface-card); margin: 5% auto; padding: 20px; border: none; border-radius: 8px; width: 60%; max-width: 600px;">
        <h3 style="color: var(--accent-gold);">Edit Produk</h3>
        <form id="editProductForm">
            <input type="hidden" id="editProductId" name="id">
            <div class="form-group">
                <label>Nama Produk</label>
                <input type="text" id="editProductName" name="name" required>
            </div>
            <div class="form-group">
                <label>Harga (Rp)</label>
                <input type="number" id="editProductPrice" name="price" required>
            </div>
            <div class="form-group">
                <label>Stok</label>
                <input type="number" id="editProductStock" name="stock_quantity" required>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select id="editProductStatus" name="status" required>
                    <option value="active">Aktif</option>
                    <option value="inactive">Tidak Aktif</option>
                </select>
            </div>
            <div class="form-group">
                <label>Deskripsi</label>
                <textarea id="editProductDescription" name="description" style="height: 80px;"></textarea>
            </div>
            <div class="form-group">
                <label>Ganti Foto Produk (Opsional)</label>
                <input type="file" id="editProductImage" name="image" accept="image/*">
                <div id="currentProductImage" style="margin-top: 10px;"></div>
            </div>
            <div style="margin-top: 1rem;">
                <button type="submit" class="btn-primary">Simpan Perubahan</button>
                <button type="button" class="btn-secondary" onclick="closeEditProductModal()">Batal</button>
            </div>
        </form>
    </div>
</div>

<script>
// Fungsi untuk menangani form tambah layanan
document.getElementById('addServiceForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    // Tambahkan CSRF token ke formData jika belum ada
    if (!formData.get('_token')) {
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    }
    
    fetch('{{ route("admin.services.create") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if(data) {
            alert('Layanan berhasil ditambahkan!');
            location.reload(); // Refresh halaman untuk menampilkan data baru
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menambahkan layanan');
    });
});

// Fungsi untuk menangani form tambah produk
document.getElementById('addProductForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    // Tambahkan CSRF token ke formData jika belum ada
    if (!formData.get('_token')) {
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    }
    
    fetch('{{ route("admin.products.create") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if(data) {
            alert('Produk berhasil ditambahkan!');
            location.reload(); // Refresh halaman untuk menampilkan data baru
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menambahkan produk');
    });
});

// Fungsi untuk edit layanan
function editService(id) {
    // Ambil data layanan dari server
    fetch('{{ route("admin.services.show", ["id" => "ID_PLACEHOLDER"]) }}'.replace('ID_PLACEHOLDER', id))
    .then(response => response.json())
    .then(service => {
        document.getElementById('editServiceId').value = service.id;
        document.getElementById('editServiceName').value = service.name;
        document.getElementById('editServicePrice').value = service.price;
        document.getElementById('editServiceDuration').value = service.duration;
        document.getElementById('editServiceDescription').value = service.description || '';
        
        const currentImg = document.getElementById('currentServiceImage');
        if (service.image_path || service.image_url) {
            const imgPath = service.image_path || service.image_url;
            const fullUrl = imgPath.startsWith('http') ? imgPath : '/' + imgPath.replace(/^\//, '');
            currentImg.innerHTML = `<p>Foto saat ini:</p><img src="${fullUrl}" style="width: 100px; border-radius: 4px;">`;
        } else {
            currentImg.innerHTML = '';
        }
        
        document.getElementById('editServiceModal').style.display = 'block';
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat mengambil data layanan');
    });
}

// Fungsi untuk edit produk
function editProduct(id) {
    // Ambil data produk dari server
    fetch('{{ route("admin.products.show", ["id" => "ID_PLACEHOLDER"]) }}'.replace('ID_PLACEHOLDER', id))
    .then(response => response.json())
    .then(product => {
        document.getElementById('editProductId').value = product.id;
        document.getElementById('editProductName').value = product.name;
        document.getElementById('editProductPrice').value = product.price;
        document.getElementById('editProductStock').value = product.stock_quantity;
        document.getElementById('editProductStatus').value = product.status;
        document.getElementById('editProductDescription').value = product.description || '';
        
        const currentImg = document.getElementById('currentProductImage');
        if (product.image_path || product.image_url) {
            const imgPath = product.image_path || product.image_url;
            const fullUrl = imgPath.startsWith('http') ? imgPath : '/' + imgPath.replace(/^\//, '');
            currentImg.innerHTML = `<p>Foto saat ini:</p><img src="${fullUrl}" style="width: 100px; border-radius: 4px;">`;
        } else {
            currentImg.innerHTML = '';
        }
        
        document.getElementById('editProductModal').style.display = 'block';
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat mengambil data produk');
    });
}

// Fungsi untuk menutup modal edit layanan
function closeEditServiceModal() {
    document.getElementById('editServiceModal').style.display = 'none';
}

// Fungsi untuk menutup modal edit produk
function closeEditProductModal() {
    document.getElementById('editProductModal').style.display = 'none';
}

// Fungsi untuk menangani form edit layanan
document.getElementById('editServiceForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const id = document.getElementById('editServiceId').value;
    const formData = new FormData(this);
    formData.append('_method', 'PUT');
    
    fetch(`/admin/services/${id}`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if(data) {
            alert('Layanan berhasil diperbarui!');
            location.reload(); // Refresh halaman untuk menampilkan perubahan
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat memperbarui layanan');
    });
});

// Fungsi untuk menangani form edit produk
document.getElementById('editProductForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const id = document.getElementById('editProductId').value;
    const formData = new FormData(this);
    formData.append('_method', 'PUT');
    
    fetch(`/admin/products/${id}`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if(data) {
            alert('Produk berhasil diperbarui!');
            location.reload(); // Refresh halaman untuk menampilkan perubahan
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat memperbarui produk');
    });
});

// Fungsi untuk hapus layanan
function deleteService(id) {
    if(confirm('Apakah Anda yakin ingin menghapus layanan ini?')) {
        fetch('{{ route("admin.services.update", ["id" => "ID_PLACEHOLDER"]) }}'.replace('ID_PLACEHOLDER', id), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if(data.message) {
                alert('Layanan berhasil dihapus!');
                location.reload(); // Refresh halaman untuk menampilkan perubahan
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menghapus layanan');
        });
    }
}

// Fungsi untuk hapus produk
function deleteProduct(id) {
    if(confirm('Apakah Anda yakin ingin menghapus produk ini?')) {
        fetch('{{ route("admin.products.update", ["id" => "ID_PLACEHOLDER"]) }}'.replace('ID_PLACEHOLDER', id), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if(data.message) {
                alert('Produk berhasil dihapus!');
                location.reload(); // Refresh halaman untuk menampilkan perubahan
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menghapus produk');
        });
    }
}

// Tutup modal saat klik di luar konten modal
window.onclick = function(event) {
    const serviceModal = document.getElementById('editServiceModal');
    const productModal = document.getElementById('editProductModal');
    
    if (event.target == serviceModal) {
        serviceModal.style.display = 'none';
    }
    
    if (event.target == productModal) {
        productModal.style.display = 'none';
    }
}
</script>
@endsection