@if(!empty($categories))
@foreach ($categories as $key => $category)
<tr>
    <td>{{ $key + 1 }}</td>
    <td>{{ $category->name ?? '-' }}</td>

    <td>
        <div class="d-flex align-items-center justify-content-center gap-1">
       <span class="badge bg-label-{{ $category->is_active ? 'success' : 'danger' }}">
    {{ $category->is_active ? 'Active' : 'Inactive' }}
</span>
    </td>


   <td class="text-center">


                                    <a href="javascript:void(0);"
                                       data-id="{{ $category->id }}"
                                       class="btn btn-icon btn-text-secondary rounded-pill waves-effect view-operation"
                                       data-bs-toggle="tooltip">
                                        <i class="icon-base ti tabler-eye"></i>
                                    </a>

                                    <a href="javascript:void(0);"
                                       data-id="{{ $category->id }}"
                                       class="btn btn-icon btn-text-secondary rounded-pill waves-effect edit-operation"
                                       data-bs-toggle="tooltip">
                                        <i class="icon-base ti tabler-edit icon-md"></i>
                                    </a>

                                    <a href="javascript:void(0);"
                                       data-id="{{ $category->id }}"
                                       class="btn btn-icon btn-text-secondary rounded-pill waves-effect delete-operation"
                                       data-bs-toggle="tooltip">
                                        <i class="icon-base ti tabler-trash icon-md text-danger"></i>
                                    </a>

                                </div>
                            </td>


</tr>
@endforeach
@endif
