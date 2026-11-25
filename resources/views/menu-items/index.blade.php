
@extends('layouts.app')

@section('title', 'Menu Items')
@section('page-title', 'Menu Items')

@section('content')
<div class="container-fluid py-3">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">Menu Items</h4>
            <p class="text-muted mb-0">Manage food, drinks, prices, and availability.</p>
        </div>

        <a href="{{ route('menu-items.create') }}"
           class="btn text-white fw-semibold"
           style="background-color: #14532d;">
            <i class="fas fa-plus me-2"></i>Add Menu Item
        </a>
    </div>

    @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"
                    aria-label="Close"></button>
        </div>
    @endif

    @if($items->count())
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0">
                    <i class="fas fa-utensils me-2" style="color: #b8860b;"></i>
                    Restaurant Menu
                </h6>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background-color: #f5f7f4;">
                        <tr>
                            <th class="px-3 py-3">#</th>
                            <th class="py-3">Item Name</th>
                            <th class="py-3">Category</th>
                            <th class="py-3">Price</th>
                            <th class="py-3 text-center">Availability</th>
                            <th class="py-3 text-end pe-3">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($items as $item)
                            <tr>
                                <td class="px-3">
                                    {{ $items->firstItem() + $loop->index }}
                                </td>

                                <td>
                                    <div class="fw-semibold">{{ $item->name }}</div>
                                    @if($item->description)
                                        <small class="text-muted">
                                            {{ $item->description }}
                                        </small>
                                    @endif
                                </td>

                                <td>
                                    {{ $item->category?->name ?? 'Unassigned' }}
                                </td>

                                <td class="fw-semibold">
                                    GH₵ {{ number_format((float) $item->price, 2) }}
                                </td>

                                <td class="text-center">
                                    @if($item->is_available)
                                        <span class="badge bg-success">Available</span>
                                    @else
                                        <span class="badge bg-secondary">Unavailable</span>
                                    @endif
                                </td>

                                <td class="text-end pe-3">
                                    <div class="d-inline-flex gap-2">
                                        <a href="{{ route('menu-items.edit', $item) }}"
                                           class="btn btn-sm btn-outline-success"
                                           title="Edit menu item">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <form action="{{ route('menu-items.toggle-availability', $item) }}"
                                              method="POST"
                                              onsubmit="return confirm('Change the availability of this item?');">
                                            @csrf
                                            @method('PATCH')

                                            <button type="submit"
                                                    class="btn btn-sm btn-outline-warning"
                                                    title="Toggle availability">
                                                <i class="fas fa-toggle-on"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="card-footer bg-white">
                {{ $items->links() }}
            </div>
        </div>
    @else
        <div class="card border-0 shadow-sm text-center py-5">
            <div class="card-body">
                <i class="fas fa-utensils fa-3x mb-3" style="color: #b8860b;"></i>
                <h5 class="fw-bold">Your menu is empty</h5>
                <p class="text-muted">Add your first food or drink item to get started.</p>

                <a href="{{ route('menu-items.create') }}"
                   class="btn text-white"
                   style="background-color: #14532d;">
                    <i class="fas fa-plus me-2"></i>Add First Menu Item
                </a>
            </div>
        </div>
    @endif

</div>
@endsection
