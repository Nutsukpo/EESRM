
@extends('layouts.app')

@section('title', 'Edit Menu Item')
@section('page-title', 'Edit Menu Item')

@section('content')
<div class="container-fluid py-3">

    <div class="mb-4">
        <a href="{{ route('menu-items.index') }}"
           class="text-decoration-none" style="color: #14532d;">
            <i class="fas fa-arrow-left me-2"></i>Back to Menu Items
        </a>
    </div>

    <div class="card border-0 shadow-sm" style="max-width: 850px;">
        <div class="card-header bg-white py-3">
            <h5 class="fw-bold mb-1">Edit Menu Item</h5>
            <p class="text-muted small mb-0">
                Update the details for {{ $item->name }}.
            </p>
        </div>

        <div class="card-body p-4">
            <form action="{{ route('menu-items.update', $item) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold">
                        Item Name <span class="text-danger">*</span>
                    </label>

                    <input type="text" id="name" name="name"
                           value="{{ old('name', $item->name) }}"
                           class="form-control @error('name') is-invalid @enderror"
                           maxlength="255" required>

                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="menu_category_id" class="form-label fw-semibold">
                        Category <span class="text-danger">*</span>
                    </label>

                    <select id="menu_category_id" name="menu_category_id"
                            class="form-select @error('menu_category_id') is-invalid @enderror"
                            required>
                        <option value="">Select a category</option>

                        @foreach($categories as $category)
                            <option value="{{ $category->id }}"
                                @selected(old('menu_category_id', $item->menu_category_id) == $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>

                    @error('menu_category_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="price" class="form-label fw-semibold">
                        Selling Price (GH₵) <span class="text-danger">*</span>
                    </label>

                    <div class="input-group">
                        <span class="input-group-text">GH₵</span>
                        <input type="number" id="price" name="price"
                               value="{{ old('price', $item->price) }}"
                               class="form-control @error('price') is-invalid @enderror"
                               min="0.01" max="9999999999.99"
                               step="0.01" required>
                        @error('price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <label for="description" class="form-label fw-semibold">
                        Description
                    </label>

                    <textarea id="description" name="description" rows="4"
                              maxlength="2000"
                              class="form-control @error('description') is-invalid @enderror"
                              placeholder="Optional details about the dish">{{ old('description', $item->description) }}</textarea>

                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Availability</label>
                    <div>
                        @if($item->is_available)
                            <span class="badge bg-success">Available</span>
                        @else
                            <span class="badge bg-secondary">Unavailable</span>
                        @endif
                        <small class="text-muted ms-2">
                            Change availability from the menu listing.
                        </small>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <button type="submit" class="btn text-white fw-semibold"
                            style="background-color: #14532d;">
                        <i class="fas fa-save me-2"></i>Update Menu Item
                    </button>

                    <a href="{{ route('menu-items.index') }}"
                       class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
