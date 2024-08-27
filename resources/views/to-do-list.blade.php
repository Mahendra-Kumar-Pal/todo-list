@extends('layouts.master')

@push('style')
    
@endpush

@section('contents')
    <div class="container">
        <div class="row">

            <div class="col-12">
                <h3 class="text-info mt-3">
                    PHP - Simple To Do List App
                </h3>
            </div>
            <hr size="2">

            <div class="col-6 offset-md-3">
                <form action="{{ route('todo-list.store') }}" method="post" id="add-task-form">
                    @csrf
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" name="task" placeholder="Add task..." aria-describedby="basic-addon2" >
                        <button type="submit" class="input-group-text bg-primary text-white" id="basic-addon2">Add Task</button>
                    </div>
                </form>
            </div>

            <div class="col-12 mb-3 text-end">
                <button id="toggle-all-status" class="btn btn-success">Toggle Task Status</button>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div id="success-alert" class="alert alert-success alert-dismissible" style="display:none;">
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                <strong>Success!</strong> Task added successfully.
            </div>

            <div class="col-12">
                <table class="table table-bordered table-striped table-hovered data-table">
                    <thead>
                        <tr>
                            <th>Sr No</th>
                            <th>Task</th>
                            <th>Status</th>
                            <th width="100px">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script type="text/javascript">
        $(function () {
            var table = $('.data-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('todo-list.todoList') }}",
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                    {data: 'task', name: 'task'},
                    {data: 'status', name: 'status'},
                    {data: 'action', name: 'action', orderable: false, searchable: false},
                ]
            });
        });
  </script>
  <script>
    $(document).ready(function() {
        //---------submit form---------
        $('#add-task-form').submit(function(e) {
            e.preventDefault();
            $('#basic-addon2').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...');

            $.ajax({
                type: 'POST',
                url: $(this).attr('action'),
                data: $(this).serialize(),
                success: function(response) {
                    $('#success-alert').show();
                    setTimeout(function() {
                        $('#success-alert').fadeOut('slow');
                    }, 5000);
                    $('input[name="task"]').val('');
                    $('.data-table').DataTable().ajax.reload();
                },
                error: function(xhr) {
                    var errors = xhr.responseJSON.errors;
                    var errorMessages = '';
                    $.each(errors, function(key, value) {
                        errorMessages += value[0] + '<br>';
                    });
                    $('input[name="task"]').focus();
                    Swal.fire(
                        'Error!',
                        errorMessages,
                        'error'
                    );
                },
                complete: function() {
                    $('#basic-addon2').prop('disabled', false).html('Add Task');
                }
            });
        });
        
        //--------------update status---------------
        $(document).on('click', '.edit', function() {
            var id = $(this).data('id');

            $.ajax({
                url: "{{ route('todo-list.updateStatus', '') }}/" + id,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire(
                            'Success!',
                            response.message,
                            'success'
                        );
                        $('.data-table').DataTable().ajax.reload();
                        $('a.edit[data-id="'+id+'"]').remove();
                    } else {
                        Swal.fire(
                            'Error!',
                            response.message,
                            'error'
                        );
                    }
                },
                error: function() {
                    Swal.fire(
                        'Error!',
                        'Something went wrong.',
                        'error'
                    );
                }
            });
        });

        //--------------delete task---------------
        $(document).on('click', '.delete', function() {
            var id = $(this).data('id');

            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('todo-list.delete', '') }}/" + id,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}',
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire(
                                    'Deleted!',
                                    response.message,
                                    'success'
                                );
                                $('.data-table').DataTable().ajax.reload();
                            } else {
                                Swal.fire(
                                    'Error!',
                                    response.message,
                                    'error'
                                );
                            }
                        },
                        error: function() {
                            Swal.fire(
                                'Error!',
                                'Something went wrong.',
                                'error'
                            );
                        }
                    });
                }
            });
        });

        //-------------Show all tasks----------------
        $('#toggle-all-status').click(function() {
            var button = $(this);
            var url = "{{ route('todo-list.markAll') }}";

            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}"
                },
                beforeSend: function() {
                    button.html('<i class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></i>').attr('disabled', true);
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire(
                            'Success!',
                            response.message,
                            'success'
                        );
                        $('.data-table').DataTable().ajax.reload();
                        button.attr('disabled', false).html('Toggle Task Status');
                    }
                },
                error: function(xhr) {
                    Swal.fire(
                        'Error!',
                        'Something went wrong!',
                        'error'
                    );
                    button.attr('disabled', false).html('Toggle Task Status');
                }
            });
        });
    });
  </script>
@endpush