
@extends('layouts.app')

@section('title', 'Edit Menu Category')
@section('page-title', 'Edit Menu Category')

@section('content')
<div class="container-fluid py-3">

    <div class="mb-4">
        <a href="{{ route('categories.index') }}"
           class="text-decoration-none"
           style="color: #14532d;">
            <i class="fas fa-arrow-left me-2"></i>Back to Categories
        </a>
    </div>

    <div class="card border-0 shadow-sm" style="max-width: 800px;">
        <div class="card-header bg-white py-3">
            <h5 class="fw-bold mb-1">Edit Category</h5>
            <p class="text-muted small mb-0">
                Update the details for {{ $category->name }}.
            </p>
        </div>

        <div class="card-body p-4">
            <form action="{{ route('categories.update', $category) }}"
                  method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold">
                        Category Name <span class="text-danger">*</span>
                    </label>

                    <input type="text"
                           name="name"
                           id="name"
                           value="{{ old('name', $category->name) }}"
                           class="form-control @error('name') is-invalid @enderror"
                           maxlength="255"
                           required>

                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="description" class="form-label fw-semibold">
                        Description
                    </label>

                    <textarea name="description"
                              id="description"
                              rows="4"
                              maxlength="2000"
                              class="form-control @error('description') is-invalid @enderror"
                              placeholder="Optional category description">{{ old('description', $category->description) }}</textarea>

                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Current Status</label>
                    <div>
                        @if($category->is_active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                        <small class="text-muted ms-2">
                            Change status from the category listing.
                        </small>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <button type="submit"
                            class="btn text-white fw-semibold"
                            style="background-color: #14532d;">
                        <i class="fas fa-save me-2"></i>Update Category
                    </button>

                    <a href="{{ route('categories.index') }}"
                       class="btn btn-outline-secondary">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
