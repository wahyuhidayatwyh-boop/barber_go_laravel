@extends('admin.admindashboard')

@section('content')
<div class="main-tab-content active" id="barber-tab-content">
    <div class="content-card">
        <h1 class="dashboard-header">Kelola Daftar <span>Barber</span></h1>
        <p style="color: var(--text-grey); margin-bottom: 2rem;">Di sini Anda dapat mengelola tim barber yang bekerja di barbershop Anda.</p>
        
        <!-- Form untuk tambah barber -->
        <div class="form-section" style="margin-bottom: 2rem; padding: 1.5rem; background: var(--surface-light);">
            <h4 style="font-size: 1.2rem; color: var(--accent-gold); margin-bottom: 1rem;">Tambah Barber Baru</h4>
            <form id="addBarberForm" enctype="multipart/form-data">
                @csrf
                <div class="form-row">
                    <div class="form-group">
                        <label>Nama Barber</label>
                        <input type="text" id="barberName" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Spesialisasi</label>
                        <input type="text" id="barberSpecialty" name="specialty" placeholder="Contoh: Fade Master, Classic Cut">
                    </div>
                    <div class="form-group">
                        <label>Rating Awal (0-5)</label>
                        <input type="number" id="barberRating" name="rating" step="0.1" min="0" max="5" value="5.0">
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select id="barberStatus" name="status" required>
                            <option value="active">Aktif</option>
                            <option value="inactive">Tidak Aktif</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Foto Barber (Upload dari Lokal)</label>
                    <input type="file" id="barberImage" name="image" accept="image/*">
                    <p style="font-size: 0.8rem; color: var(--text-grey); margin-top: 5px;">Pilih file gambar dari komputer Anda.</p>
                </div>
                <button type="submit" class="btn-primary" style="margin-top: 1rem;">Tambah Barber</button>
            </form>
        </div>
        
        <h4 style="font-size: 1.5rem; border-top: var(--border-subtle); padding-top: 2rem; margin-top: 2rem; color: var(--accent-gold);">Tim Barber</h4>
        <div class="product-grid" id="barberGrid">
            @if(isset($barbers) && $barbers->count() > 0)
                @foreach($barbers as $barber)
                    <div class="product-card" data-id="{{ $barber->id }}">
                        <img class="product-card-img" src="{{ asset($barber->image_path ?? $barber->image_url ?? 'assets/img/default-barber.jpg') }}" alt="{{ $barber->name }}" style="height: 200px; object-fit: cover; border-radius: 8px 8px 0 0;">
                        <div style="padding: 1.2rem;">
                            <h3 style="margin-bottom: 0.5rem;">{{ $barber->name }}</h3>
                            <p style="font-size: 0.9rem; color: var(--text-grey); margin-bottom: 0.5rem;">{{ $barber->specialty ?? 'General Barber' }}</p>
                            <div style="display: flex; align-items: center; gap: 5px; margin-bottom: 1rem;">
                                <i class="fas fa-star" style="color: var(--accent-gold);"></i>
                                <span>{{ number_format($barber->rating, 1) }}</span>
                            </div>
                            <span class="stock-status {{ $barber->status == 'active' ? 'ready' : 'out-of-stock' }}">{{ $barber->status == 'active' ? 'Aktif' : 'Non-aktif' }}</span>
                            <div class="product-actions" style="margin-top: 1rem;">
                                <button class="btn-small btn-edit" onclick="editBarber({{ $barber->id }})">Edit</button>
                                <button class="btn-small btn-delete" onclick="deleteBarber({{ $barber->id }})">Hapus</button>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <p style="color: var(--text-grey); text-align: center; padding: 2rem; width: 100%;">Belum ada data barber</p>
            @endif
        </div>
    </div>
</div>

<!-- Modal untuk edit barber -->
<div id="editBarberModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.4);">
    <div class="modal-content" style="background-color: var(--surface-card); margin: 5% auto; padding: 20px; border: none; border-radius: 8px; width: 60%; max-width: 600px;">
        <h3 style="color: var(--accent-gold);">Edit Data Barber</h3>
        <form id="editBarberForm" enctype="multipart/form-data">
            @csrf
            <input type="hidden" id="editBarberId" name="id">
            <div class="form-group">
                <label>Nama Barber</label>
                <input type="text" id="editBarberName" name="name" required>
            </div>
            <div class="form-group">
                <label>Spesialisasi</label>
                <input type="text" id="editBarberSpecialty" name="specialty">
            </div>
            <div class="form-group">
                <label>Rating</label>
                <input type="number" id="editBarberRating" name="rating" step="0.1" min="0" max="5">
            </div>
            <div class="form-group">
                <label>Status</label>
                <select id="editBarberStatus" name="status" required>
                    <option value="active">Aktif</option>
                    <option value="inactive">Tidak Aktif</option>
                </select>
            </div>
            <div class="form-group">
                <label>Ganti Foto Barber (Opsional)</label>
                <input type="file" id="editBarberImage" name="image" accept="image/*">
                <div id="currentBarberImage" style="margin-top: 10px;"></div>
            </div>
            <div style="margin-top: 1rem;">
                <button type="submit" class="btn-primary">Simpan Perubahan</button>
                <button type="button" class="btn-secondary" onclick="closeEditBarberModal()">Batal</button>
            </div>
        </form>
    </div>
</div>

<script>
// Handle add barber
document.getElementById('addBarberForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('{{ route("admin.barbers.create") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if(data) {
            alert('Barber berhasil ditambahkan!');
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menambahkan barber');
    });
});

// Handle edit click
function editBarber(id) {
    fetch(`/admin/barbers/${id}`) // Note: Ensure you have a show route for barber or use getAll and filter
    .then(response => response.json())
    .then(barber => {
        // Fallback: If no direct show route, we might need to get it differently
        // But for now assuming AdminController has a getBarber or similar
    })
    .catch(err => {
        // Since we might not have a direct show route yet, let's just use the data from the page if needed
        // Or better, let's ensure AdminController has a getBarber method
    });
    
    // For now, let's implement a generic way or add the method to AdminController
}

function openEditModal(barber) {
    document.getElementById('editBarberId').value = barber.id;
    document.getElementById('editBarberName').value = barber.name;
    document.getElementById('editBarberSpecialty').value = barber.specialty || '';
    document.getElementById('editBarberRating').value = barber.rating;
    document.getElementById('editBarberStatus').value = barber.status;
    
    const currentImg = document.getElementById('currentBarberImage');
    if (barber.image_path || barber.image_url) {
        const imgPath = barber.image_path || barber.image_url;
        const fullUrl = imgPath.startsWith('http') ? imgPath : '/' + imgPath.replace(/^\//, '');
        currentImg.innerHTML = `<p>Foto saat ini:</p><img src="${fullUrl}" style="width: 100px; border-radius: 4px;">`;
    } else {
        currentImg.innerHTML = '';
    }
    
    document.getElementById('editBarberModal').style.display = 'block';
}

// We need a way to get barber data for edit. Let's add a function to get it.
function editBarber(id) {
    // If we don't have a specific route, we can fetch all and find
    fetch('{{ route("admin.barbers") }}')
    .then(response => response.json())
    .then(barbers => {
        const barber = barbers.find(b => b.id == id);
        if(barber) {
            openEditModal(barber);
        }
    });
}

function closeEditBarberModal() {
    document.getElementById('editBarberModal').style.display = 'none';
}

// Handle edit form submit
document.getElementById('editBarberForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const id = document.getElementById('editBarberId').value;
    const formData = new FormData(this);
    formData.append('_method', 'PUT');
    
    fetch(`/admin/barbers/${id}`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if(data) {
            alert('Data barber berhasil diperbarui!');
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat memperbarui data barber');
    });
});

// Handle delete
function deleteBarber(id) {
    if(confirm('Apakah Anda yakin ingin menghapus barber ini?')) {
        fetch(`/admin/barbers/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            alert('Barber berhasil dihapus!');
            location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menghapus barber');
        });
    }
}

window.onclick = function(event) {
    const modal = document.getElementById('editBarberModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}
</script>
@endsection
