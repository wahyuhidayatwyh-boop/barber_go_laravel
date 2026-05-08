@extends('admin.admindashboard')

@section('content')
<div class="main-tab-content active" id="banners-tab-content">
    <div class="content-card">
        <h1 class="dashboard-header">Kelola <span>Banners</span></h1>
        <p style="color: var(--text-grey); margin-bottom: 2rem;">Di sini Anda dapat mengelola banner yang akan ditampilkan di aplikasi mobile.</p>
        
        <!-- Form untuk tambah banner -->
        <div class="form-section" style="margin-bottom: 2rem; padding: 1.5rem; background: var(--surface-light);">
            <h4 style="font-size: 1.2rem; color: var(--accent-gold); margin-bottom: 1rem;">Tambah Banner Baru</h4>
            <form id="addBannerForm" enctype="multipart/form-data">
                @csrf
                <div class="form-row">
                    <div class="form-group">
                        <label>Judul Banner</label>
                        <input type="text" id="bannerTitle" name="title" required>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select id="bannerActive" name="is_active">
                            <option value="1">Aktif</option>
                            <option value="0">Tidak Aktif</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea id="bannerDescription" name="description" style="height: 80px;"></textarea>
                </div>
                <div class="form-group">
                    <label>Foto Banner (Upload dari Galeri/Lokal)</label>
                    <input type="file" id="bannerImage" name="image" accept="image/*" required>
                    <p style="font-size: 0.8rem; color: var(--text-grey); margin-top: 5px;">Pilih file gambar dari komputer Anda.</p>
                </div>
                <button type="submit" class="btn-primary" style="margin-top: 1rem;">Tambah Banner</button>
            </form>
        </div>
        
        <h4 style="font-size: 1.5rem; border-top: var(--border-subtle); padding-top: 2rem; margin-top: 2rem; color: var(--accent-gold);">Daftar Banner</h4>
        <div class="product-grid" id="bannerGrid">
            @if(isset($banners) && $banners->count() > 0)
                @foreach($banners as $banner)
                    <div class="product-card" data-id="{{ $banner->id }}">
                        <img class="product-card-img" src="{{ asset($banner->image_path ?? $banner->image_url ?? 'assets/img/default-banner.jpg') }}" alt="{{ $banner->title }}" style="height: 150px; object-fit: cover;">
                        <h3>{{ $banner->title }}</h3>
                        <p style="font-size: 0.9rem; color: var(--text-grey); margin-bottom: 10px;">{{ Str::limit($banner->description, 50) }}</p>
                        <span class="stock-status {{ $banner->is_active ? 'ready' : 'out-of-stock' }}">{{ $banner->is_active ? 'Aktif' : 'Non-aktif' }}</span>
                        <div class="product-actions">
                            <button class="btn-small btn-edit" onclick="editBanner({{ $banner->id }})">Edit</button>
                            <button class="btn-small btn-delete" onclick="deleteBanner({{ $banner->id }})">Hapus</button>
                        </div>
                    </div>
                @endforeach
            @else
                <p style="color: var(--text-grey); text-align: center; padding: 2rem; width: 100%;">Tidak ada banner</p>
            @endif
        </div>
    </div>
</div>

<!-- Modal untuk edit banner -->
<div id="editBannerModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.4);">
    <div class="modal-content" style="background-color: var(--surface-card); margin: 5% auto; padding: 20px; border: none; border-radius: 8px; width: 60%; max-width: 600px;">
        <h3 style="color: var(--accent-gold);">Edit Banner</h3>
        <form id="editBannerForm" enctype="multipart/form-data">
            @csrf
            <input type="hidden" id="editBannerId" name="id">
            <div class="form-group">
                <label>Judul Banner</label>
                <input type="text" id="editBannerTitle" name="title" required>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select id="editBannerActive" name="is_active">
                    <option value="1">Aktif</option>
                    <option value="0">Tidak Aktif</option>
                </select>
            </div>
            <div class="form-group">
                <label>Deskripsi</label>
                <textarea id="editBannerDescription" name="description" style="height: 80px;"></textarea>
            </div>
            <div class="form-group">
                <label>Ganti Foto Banner (Opsional)</label>
                <input type="file" id="editBannerImage" name="image" accept="image/*">
                <div id="currentBannerImage" style="margin-top: 10px;"></div>
            </div>
            <div style="margin-top: 1rem;">
                <button type="submit" class="btn-primary">Simpan Perubahan</button>
                <button type="button" class="btn-secondary" onclick="closeEditBannerModal()">Batal</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('addBannerForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('{{ route("admin.banners.create") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if(data) {
            alert('Banner berhasil ditambahkan!');
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menambahkan banner');
    });
});

function editBanner(id) {
    fetch('{{ route("admin.banners.show", ["id" => "ID_PLACEHOLDER"]) }}'.replace('ID_PLACEHOLDER', id))
    .then(response => response.json())
    .then(banner => {
        document.getElementById('editBannerId').value = banner.id;
        document.getElementById('editBannerTitle').value = banner.title;
        document.getElementById('editBannerActive').value = banner.is_active ? '1' : '0';
        document.getElementById('editBannerDescription').value = banner.description || '';
        
        const currentImg = document.getElementById('currentBannerImage');
        if (banner.image_path || banner.image_url) {
            const imgPath = banner.image_path || banner.image_url;
            const fullUrl = imgPath.startsWith('http') ? imgPath : '/' + imgPath.replace(/^\//, '');
            currentImg.innerHTML = `<p>Foto saat ini:</p><img src="${fullUrl}" style="width: 100px; border-radius: 4px;">`;
        } else {
            currentImg.innerHTML = '';
        }
        
        document.getElementById('editBannerModal').style.display = 'block';
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat mengambil data banner');
    });
}

function closeEditBannerModal() {
    document.getElementById('editBannerModal').style.display = 'none';
}

document.getElementById('editBannerForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const id = document.getElementById('editBannerId').value;
    const formData = new FormData(this);
    
    // Use POST with _method for update since it has files
    fetch(`/admin/banners/${id}`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if(data) {
            alert('Banner berhasil diperbarui!');
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat memperbarui banner');
    });
});

function deleteBanner(id) {
    if(confirm('Apakah Anda yakin ingin menghapus banner ini?')) {
        fetch(`/admin/banners/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if(data.message) {
                alert('Banner berhasil dihapus!');
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menghapus banner');
        });
    }
}

window.onclick = function(event) {
    const bannerModal = document.getElementById('editBannerModal');
    if (event.target == bannerModal) {
        bannerModal.style.display = 'none';
    }
}
</script>
@endsection
