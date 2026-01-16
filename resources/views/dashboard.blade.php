<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AtoZ Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">AtoZ Backend</a>
            <div class="d-flex">
                <span class="navbar-text me-3">Role: {{ auth()->user()->role }}</span>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button class="btn btn-outline-light btn-sm" type="submit">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if(session('info'))
        <div class="alert alert-info">{{ session('info') }}</div>
        @endif
        @if($errors->any())
        <div class="alert alert-danger">
            <ul>@foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul>
        </div>
        @endif

        <!-- Tabs -->
        <ul class="nav nav-tabs mb-4" id="myTab" role="tablist">
            @if(auth()->user()->isAdmin())
            <li class="nav-item">
                <button class="nav-link active" id="products-tab" data-bs-toggle="tab" data-bs-target="#products" type="button">Products</button>
            </li>
            @endif
            <li class="nav-item">
                <button class="nav-link {{ !auth()->user()->isAdmin() ? 'active' : '' }}" id="orders-tab" data-bs-toggle="tab" data-bs-target="#orders" type="button">Orders</button>
            </li>
            @if(auth()->user()->isAdmin())
            <li class="nav-item">
                <button class="nav-link" id="staff-tab" data-bs-toggle="tab" data-bs-target="#staff" type="button">Staff</button>
            </li>
            @endif
        </ul>

        <div class="tab-content" id="myTabContent">

            <!-- Products Tab -->
            @if(auth()->user()->isAdmin())
            <div class="tab-pane fade show active" id="products" role="tabpanel">
                <div class="d-flex justify-content-between mb-3">
                    <h4>Product Management</h4>
                    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#productModal">+ New Product</button>
                </div>
                <table class="table table-striped table-bordered bg-white">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>SKU</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $p)
                        <tr>
                            <td>{{ $p->id }}</td>
                            <td>{{ $p->name }}</td>
                            <td>{{ $p->sku }}</td>
                            <td>${{ $p->price }}</td>
                            <td>{{ $p->stock_quantity }}</td>
                            <td><span class="badge bg-{{ $p->status==='active'?'success':'secondary' }}">{{ $p->status }}</span></td>
                            <td>
                                <form action="{{ route('products.destroy', $p->id) }}" method="POST" onsubmit="return confirm('Delete?')" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Del</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="d-flex justify-content-center mt-3">
                    {{ $products->links() }}
                </div>
            </div>
            @endif

            <!-- Orders Tab -->
            <div class="tab-pane fade {{ !auth()->user()->isAdmin() ? 'show active' : '' }}" id="orders" role="tabpanel">
                <div class="d-flex justify-content-between mb-3">
                    <h4>Order Management</h4>
                    @if(auth()->user()->isAdmin())
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#orderModal">+ New Order</button>
                    @endif
                </div>
                <table class="table table-hover bg-white">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Shipment</th>
                            <th>Staff</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $o)
                        <tr>
                            <td>{{ $o->id }}</td>
                            <td>{{ $o->user->name ?? 'N/A' }}</td>
                            <td>${{ $o->total_amount }}</td>
                            <td>
                                <form action="{{ route('orders.updateStatus', $o->id) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <select name="status" onchange="this.form.submit()" class="form-select form-select-sm" {{ $o->status==='cancelled'?'disabled':'' }}>
                                        <option value="pending" {{ $o->status=='pending'?'selected':'' }}>Pending</option>
                                        <option value="confirmed" {{ $o->status=='confirmed'?'selected':'' }}>Confirmed</option>
                                        <option value="shipped" {{ $o->status=='shipped'?'selected':'' }}>Shipped</option>
                                        <option value="cancelled" {{ $o->status=='cancelled'?'selected':'' }}>Cancelled</option>
                                    </select>
                                </form>
                            </td>
                            <td>
                                @if($o->shipment)
                                <span class="badge bg-info" title="Tracking: {{ $o->shipment->tracking_number }}">Shipped</span>
                                @else - @endif
                            </td>
                            <td>{{ $o->staff->name ?? $o->assigned_staff_id }}</td>
                            <td>
                                <button class="btn btn-warning btn-sm" onclick="alert(`Items:\n{{ $o->items->map(fn($i) => ($i->product?->name ?? 'Deleted Item') . ' x' . $i->quantity)->join('\n') }}`)">Items</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="d-flex justify-content-center mt-3">
                    {{ $orders->links() }}
                </div>
            </div>

            <!-- Staff Tab -->
            @if(auth()->user()->isAdmin())
            <div class="tab-pane fade" id="staff" role="tabpanel">
                <div class="d-flex justify-content-between mb-3">
                    <h4>Staff Management</h4>
                    <button class="btn btn-warning btn-sm" onclick="showCreateStaff()">+ New Staff</button>
                </div>
                <table class="table table-striped bg-white">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($staffMembers as $u)
                        <tr>
                            <td>{{ $u->id }}</td>
                            <td>{{ $u->name }}</td>
                            <td>{{ $u->email }}</td>
                            <td>
                                <button class="btn btn-sm btn-info me-2" onclick="showEditStaff({{ $u->id }}, '{{ $u->name }}', '{{ $u->email }}')">Edit</button>
                                <form action="{{ route('staff.destroy', $u->id) }}" method="POST" onsubmit="return confirm('Delete?')" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Del</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>

    <!-- Modals -->
    <!-- Create Product Modal -->
    <div class="modal fade" id="productModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('products.store') }}" method="POST" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">New Product</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="text" name="name" class="form-control mb-2" placeholder="Name" required>
                    <input type="text" name="sku" class="form-control mb-2" placeholder="SKU" required>
                    <input type="number" step="0.01" name="price" class="form-control mb-2" placeholder="Price" required>
                    <input type="number" name="stock_quantity" class="form-control mb-2" placeholder="Stock" required>
                    <select name="status" class="form-control">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary">Save</button></div>
            </form>
        </div>
    </div>

    <!-- Create Staff Modal -->
    <div class="modal fade" id="staffModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="staffForm" action="{{ route('staff.store') }}" method="POST" class="modal-content">
                @csrf
                <div id="methodField"></div>
                <div class="modal-header">
                    <h5 class="modal-title" id="staffModalTitle">New Staff Member</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="text" name="name" id="s_name" class="form-control mb-2" placeholder="Full Name" required>
                    <input type="email" name="email" id="s_email" class="form-control mb-2" placeholder="Email Address" required>
                    <input type="password" name="password" id="s_password" class="form-control mb-2" placeholder="Password (Leave empty to keep current if editing)">
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary">Save Staff</button></div>
            </form>
        </div>
    </div>

    <!-- Create Order Modal -->
    <div class="modal fade" id="orderModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form action="{{ route('orders.store') }}" method="POST" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">New Order</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <h6>Select Products</h6>
                    <div class="mb-3 border p-2" style="max-height: 200px; overflow-y: auto;">
                        @foreach($activeProducts as $ap)
                        <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                            <div>
                                <strong>{{ $ap->name }}</strong> <span class="text-muted">(${{ $ap->price }})</span><br>
                                <small class="text-secondary">Available Stock: {{ $ap->stock_quantity }}</small>
                            </div>
                            <!-- Using array index for submission -->
                            <div class="d-flex align-items-center">
                                <label class="me-2 small fw-bold">Qty:</label>
                                <input type="hidden" name="products[{{ $loop->index }}][id]" value="{{ $ap->id }}">
                                <input type="number"
                                    name="products[{{ $loop->index }}][quantity]"
                                    class="form-control text-center border-secondary fw-bold"
                                    style="width: 80px;"
                                    placeholder="0"
                                    min="0"
                                    max="{{ $ap->stock_quantity }}">
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="mb-3">
                        <label>Assign Staff</label>
                        <select name="assigned_staff_id" class="form-control" required>
                            <option value="">Select Staff Member</option>
                            @foreach($availableStaff as $s)
                            <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->email }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <!-- Note: The controller expects valid products with qty > 0.
                     Products with qty 0 or null might cause issues if not filtered.
                     For a simple PHP form, we might submit all and filter in controller, or use JS to remove empty ones.
                     Given "No JS logic", I'll trust the controller or add a tiny JS to disable empty inputs before submit.
                     Actually, I'll add a tiny script to disable empty quantity inputs on submit to keep payload clean. -->
                <div class="modal-footer"><button type="submit" class="btn btn-primary" onclick="cleanOrderForm(this.form)">Place Order</button></div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Minimal UI Helper Scripts (No AJX Fetching Logic)

        function showCreateStaff() {
            document.getElementById('staffForm').action = "{{ route('staff.store') }}";
            document.getElementById('methodField').innerHTML = '';
            document.getElementById('staffModalTitle').innerText = 'New Staff Member';
            document.getElementById('s_name').value = '';
            document.getElementById('s_email').value = '';
            new bootstrap.Modal(document.getElementById('staffModal')).show();
        }

        function showEditStaff(id, name, email) {
            document.getElementById('staffForm').action = "/staff/" + id;
            document.getElementById('methodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
            document.getElementById('staffModalTitle').innerText = 'Edit Staff Member';
            document.getElementById('s_name').value = name;
            document.getElementById('s_email').value = email;
            new bootstrap.Modal(document.getElementById('staffModal')).show();
        }

        function cleanOrderForm(form) {
            // Optional: Filter out 0 quantity inputs to match validation rules
            // But validation says "products" array required.
            // If we submit inputs with 0, validation might fail "min:1".
            // So we should remove them.
            // A simple way is to not name them unless they have value? Too late.
            // I'll leave as is, but if it fails, I'll note. 
            // Actually, Controller expects `products.*.quantity` min 1.
            // So we MUST filter them.
            // I will use a tiny JS to disable inputs with 0 value before submit.
            const inputs = form.querySelectorAll('input[type="number"]');
            inputs.forEach(i => {
                if (i.value == 0 || i.value == "") {
                    i.disabled = true;
                    // Also disable the hidden ID input sibling
                    const sibling = i.previousElementSibling;
                    if (sibling && sibling.type === 'hidden') sibling.disabled = true;
                }
            });
        }
    </script>
</body>

</html>