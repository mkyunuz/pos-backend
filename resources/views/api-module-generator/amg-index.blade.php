@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-body">
                    
                <form method="post" id="form">
                    <!-- <div class='row'>
                        <div class='col-sm-4'>    
                            <div class="form-group">
                                <label for="module_name">Module Name</label>
                                <input class="form-control" id="module_name" name="module_name" size="30" type="text" />
                            </div>
                        </div>
                    </div> -->
                    <div class='row'>
                        <div class='col-sm-4'>    
                            <div class="form-group">
                                <label for="module_name">Modul Name</label>
                                <input class="form-control" id="module_name" name="module_name" size="30" type="text" />
                            </div>
                        </div>
                    </div>
                    <!-- <div class="row"> -->
                        <div class="m-3">
                            
                        <table class="table" id="table-column">
                            <thead>
                                <tr>
                                    <th>Column Name</th>
                                    <th>Column Type</th>
                                    <th>Length</th>
                                    <th>Primary</th>
                                    <th>AutoIncrement</th>
                                    <th colspan="3">Relation</th>
                                    <th>Nullable</th>
                                    <th>Unique</th>
                                    <th>Visible</th>
                                    <th>Searchable</th>
                                    <th>#</th>
                                </tr>
                            </thead>
                            <tbody>
                                
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="13" class="text-center">
                                        <button type="button" class="btn btn-success btn-sm" id="add-column">Add Column</button>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                        </div>
                    <!-- </div> -->
                    <div class='row'>
                        <div class='col-sm-4'>    
                            <div class="form-group">
                                <label for="controller_name">Controller Name</label>
                                <input class="form-control" id="controller_name" name="controller_name" size="30" type="text" />
                            </div>
                        </div>
                        <div class='col-sm-4'>    
                            <div class="form-group">
                                <label for="controller_path">Controller Path</label>
                                <input class="form-control" id="controller_path" name="controller_path" size="30" type="text" />
                            </div>
                        </div>
                        <div class='col-sm-4'>    
                            <div class="form-group">
                                <label for="route_group">Route Group</label>
                                <input class="form-control" id="route_group" name="route_group" size="30" type="text" />
                            </div>
                        </div>
                    </div>
                    <button class="btn btn-success">Generate</button>
                </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
    <script src="{{ asset('js/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('js/additional-methods.min.js') }}"></script>
    <script src="{{ asset('js/generator.js') }}"></script>
@endpush


