@extends('layouts.app')

@section('title', 'Menu Categories')
@section('page-title', 'Menu Categories')

@section('content')
<div class="container-fluid py-3">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">Menu Categories</h4>
            <p class="text-muted mb-0">
                Organize the food and drinks on your restaurant menu.
            </p>
        </div>

        <a href="{{ route('categories.create') }}"
           class="btn text-white fw-semibold"
           style="background-color: #14532d;">
            <i class="fas fa-plus me-2"></i> Add Category
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

    @if($categories->count())
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">
                    <i class="fas fa-layer-group me-2" style="color: #b8860b;"></i>
                    All Categories
                </h6>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background-color: #f5f7f4;">
                        <tr>
                            <th class="px-3 py-3">#</th>
                            <th class="py-3">Category Name</th>
                            <th class="py-3">Description</th>
                            <th class="py-3 text-center">Menu Items</th>
                            <th class="py-3 text-center">Status</th>
                            <th class="py-3 text-end pe-3">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($categories as $category)
                            <tr>
                                <td class="px-3">
                                    {{ $categories->firstItem() + $loop->index }}
                                </td>

                                <td>
                                    <span class="fw-semibold">
                                        {{ $category->name }}
                                    </span>
                                </td>

                                <td>
                                    <span class="text-muted">
                                        {{ $category->description ?: 'No description' }}
                                    </span>
                                </td>

                                <td class="text-center">
                                    <span class="badge rounded-pill text-dark"
                                          style="background-color: #f3e8c8;">
                                        {{ $category->menu_items_count }}
                                    </span>
                                </td>

                                <td class="text-center">
                                    @if($category->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>

                                <td class="text-end pe-3">
                                    <div class="d-inline-flex gap-2">
                                        <a href="{{ route('categories.edit', $category) }}"
                                           class="btn btn-sm btn-outline-success"
                                           title="Edit category">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <form action="{{ route('categories.toggle-status', $category) }}"
                                              method="POST"
                                              onsubmit="return confirm('Are you sure you want to change this category status?');">
                                            @csrf
                                            @method('PATCH')

                                            <button type="submit"
                                                    class="btn btn-sm btn-outline-warning"
                                                    title="Toggle status">
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
                {{ $categories->links() }}
            </div>
        </div>
    @else
        <div class="card border-0 shadow-sm text-center py-5">
            <div class="card-body">
                <i class="fas fa-layer-group fa-3x mb-3"
                   style="color: #b8860b;"></i>
                <h5 class="fw-bold">No categories yet</h5>
                <p class="text-muted">
                    Create your first category to start organizing menu items.
                </p>
                <a href="{{ route('categories.create') }}"
                   class="btn text-white"
                   style="background-color: #14532d;">
                    <i class="fas fa-plus me-2"></i>Create First Category
                </a>
            </div>
        </div>
    @endif

</div>
@endsection
